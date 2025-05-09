<?php

namespace App\Controller;

use App\DTO\SeriesCreationInputDTO;
use App\Entity\Series;
use App\Form\SeriesEditType;
use App\Form\SeriesType;
use App\Message\SeriesWasCreated;
use App\Message\SeriesWasDeleted;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository       $seriesRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface    $messenger,
        private SluggerInterface       $slugger,
        private TranslatorInterface    $translator
    )
    {
    }

    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function seriesList(Request $request): Response
    {
        $seriesList = $this->seriesRepository->findAll();
        $session = $request->getSession();
        $successMessage = $session->get('success');
        $session->remove('success');

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
            'successMessage' => $successMessage,
        ]);
    }

    #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm(): Response
    {
        $seriesForm = $this->createForm(SeriesType::class, new SeriesCreationInputDTO());
        return $this->renderForm('series/form.html.twig', compact('seriesForm'));
    }

    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request): Response
    {
        $input = new SeriesCreationInputDTO();
        $seriesForm = $this->createForm(SeriesType::class, $input)
            ->handleRequest($request);

        if (!$seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm'));
        }

        /** @var UploadedFile $uploadedCoverImage */
        $uploadedCoverImage = $seriesForm->get('coverImage')->getData();
        if ($uploadedCoverImage) {
            $originalFilename = pathinfo($uploadedCoverImage->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedCoverImage->guessExtension();
            $uploadedCoverImage->move($this->getParameter('cover_image_directory'), $newFilename);
            $input->coverImage = $newFilename;
        }

        $series = $this->seriesRepository->add($input);
        $this->messenger->dispatch(new SeriesWasCreated($series));

        $this->addFlash('success', $this->translator->trans('series.added.msg', ['name' => $series->getName()]));

        return $this->redirectToRoute('app_series');
    }

    #[Route(
        '/series/delete/{series}',
        name: 'app_delete_series',
        methods: ['DELETE']
    )]
    public function deleteSeries(Series $series): Response
    {
        $this->seriesRepository->remove($series, true);
        $this->messenger->dispatch(new SeriesWasDeleted($series));
        $this->addFlash('success', $this->translator->trans('series.delete'));

        return $this->redirectToRoute('app_series');
    }

    #[Route('/series/edit/{series}', name: 'app_edit_series_form', methods: ['GET'])]
    public function editSeriesForm(Series $series): Response
    {
        $seriesForm = $this->createForm(SeriesEditType::class, $series);
        return $this->renderForm('series/edit.html.twig', compact('seriesForm', 'series'));
    }

    #[Route('/series/edit/{series}', name: 'app_store_series_changes', methods: ['PATCH'])]
    public function storeSeriesChanges(Series $series, Request $request): Response
    {
        $seriesForm = $this->createForm(SeriesEditType::class, $series);
        $seriesForm->handleRequest($request);

        if (!$seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm', 'series'));
        }

        $this->addFlash('success', "Série \"{$series->getName()}\" editada com sucesso");
        $this->entityManager->flush();

        return $this->redirectToRoute('app_series');
    }
}

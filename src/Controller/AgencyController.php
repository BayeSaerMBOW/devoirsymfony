<?php
namespace App\Controller;

use App\Entity\Agency;
use App\Form\AgencyType;
use App\Form\AgencySearchType;
use App\Repository\AgencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agency')]
class AgencyController extends AbstractController
{
    #[Route('/', name: 'app_agency_index', methods: ['GET', 'POST'])] // Ajout de POST ici
    public function index(
        Request $request, 
        AgencyRepository $agencyRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $searchForm = $this->createForm(AgencySearchType::class, null, [
            'method' => 'GET'  // Changement de la méthode du formulaire en GET
        ]);
        
        $searchForm->handleRequest($request);

        $queryBuilder = $agencyRepository->createQueryBuilder('a');

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            
            if (!empty($data['telephone'])) {
                $queryBuilder
                    ->andWhere('a.telephone LIKE :telephone')
                    ->setParameter('telephone', '%' . $data['telephone'] . '%');
            }
            
            if (!empty($data['adresse'])) {
                $queryBuilder
                    ->andWhere('a.adresse LIKE :adresse')
                    ->setParameter('adresse', '%' . $data['adresse'] . '%');
            }
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            2
        );

        return $this->render('agency/index.html.twig', [
            'agencies' => $pagination,
            'searchForm' => $searchForm->createView()
        ]);
    }

    #[Route('/new', name: 'app_agency_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $agency = new Agency();
        $form = $this->createForm(AgencyType::class, $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($agency);
            $entityManager->flush();

            return $this->redirectToRoute('app_agency_index');
        }

        return $this->render('agency/new.html.twig', [
            'agency' => $agency,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name: 'app_agency_show', methods: ['GET'])]
    public function show(Agency $agency): Response
    {
        return $this->render('agency/show.html.twig', [
            'agency' => $agency
        ]);
    }

    #[Route('/{id}', name: 'app_agency_delete', methods: ['POST'])]
    public function delete(Request $request, Agency $agency, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($agency);
        $entityManager->flush();

        return $this->redirectToRoute('app_agency_index');
    }
}
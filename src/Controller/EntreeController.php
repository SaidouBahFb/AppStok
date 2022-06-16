<?php

namespace App\Controller;

use App\Entity\Entree;
use App\Entity\Produit;
use App\Form\EntreeType;
use App\Repository\EntreeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/entree')]
class EntreeController extends AbstractController
{
    #[Route('/', name: 'app_entree_index', methods: ['GET'])]
    public function index(EntreeRepository $entreeRepository): Response
    {
        return $this->render('entree/index.html.twig', [
            'entrees' => $entreeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_entree_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntreeRepository $entreeRepository, ManagerRegistry $doctrine): Response
    {
        $entree = new Entree();
        $p = new Produit();
        $form = $this->createForm(EntreeType::class, $entree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $e = $form->getData();
            $em = $doctrine->getManager();
            $em->persist($e);
            $p = $em->getRepository(Produit::class)->find($e->getProduit()->getId());
            $stock = $p->getStock() + $e->getQteE();
            $em->flush();
            $p->setStock($stock);
            $em->flush();
            $this->addFlash('info', 'Entrée enrégistrée avec success');
            //dd($stock);
            //dd($stock);
            //$per = $produitRepository->findBy(['id'=> $id]);
            //$p->$per->($stock);
            //dd($per);
            //$entreeRepository->add($entree, true);
            

            return $this->redirectToRoute('app_entree_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('entree/new.html.twig', [
            'entree' => $entree,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entree_show', methods: ['GET'])]
    public function show(Entree $entree): Response
    {
        return $this->render('entree/show.html.twig', [
            'entree' => $entree,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_entree_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entree $entree, EntreeRepository $entreeRepository): Response
    {
        $form = $this->createForm(EntreeType::class, $entree);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entreeRepository->add($entree, true);
            $this->addFlash('info', 'Entrée modifiée avec success');


            return $this->redirectToRoute('app_entree_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('entree/edit.html.twig', [
            'entree' => $entree,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entree_delete', methods: ['POST'])]
    public function delete(Request $request, Entree $entree, EntreeRepository $entreeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entree->getId(), $request->request->get('_token'))) {
            $entreeRepository->remove($entree, true);
            $this->addFlash('info', 'Entrée supprimée avec success');

        }

        return $this->redirectToRoute('app_entree_index', [], Response::HTTP_SEE_OTHER);
    }
}

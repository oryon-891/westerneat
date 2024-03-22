<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_account_product', methods: ["GET", "POST"])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        if($request->query->get('query') != null){
            $query = $request->query->get('query');
            $products = $entityManager->getRepository(Product::class)->findByCode($query);
        }else{
            $products = $entityManager->getRepository(Product::class)->findAll();
        }
        

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product_delete/{id}', name: 'app_account_product_delete')]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {

        $product = $entityManager->getRepository(Product::class)->find($id);


        $entityManager->remove($product);
        $entityManager->flush();
        

        return $this->redirectToRoute('app_account_product');
    }

    #[Route('/product/new', name: 'app_product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {

        $form = $this->createForm(ProductType::class);
        $route = $request->files->get("product");

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('illustration')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('products_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $route = $e->getMessage();
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setIllustration($newFilename);
            }

            $product->setCode("56000");
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('app_products');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'route' => $route
        ]);
    }
}

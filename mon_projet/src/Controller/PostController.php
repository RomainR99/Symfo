<?php
// src/Controller/PostController.php
namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    #[Route('/post/new', name: 'post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        // Traitement du formulaire
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $this->handleImageUpload($form, $post);

            // Persister le post en base de données
            $em->persist($post);
            $em->flush();

            // Redirection vers la page d'affichage des posts
            return $this->redirectToRoute('posts_list');
        }

        // Récupérer tous les posts depuis la base de données
        $posts = $em->getRepository(Post::class)->findAll();

        // Rendre la vue avec le formulaire et la liste des posts
        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts,  // Passer tous les posts à la vue
        ]);
    }

    #[Route('/posts', name: 'posts_list')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer tous les posts depuis la base de données
        $posts = $em->getRepository(Post::class)->findAll();

        // Rendre la vue avec la liste des posts
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    // Méthode pour gérer l'upload de l'image
    private function handleImageUpload($form, $post): void
    {
        $imageFile = $form->get('new_image')->getData();
        
        if ($imageFile) {
            // Générer un nom unique pour l'image
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();

            // Déplacer l'image dans le répertoire public/uploads/images
            try {
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer l'erreur si l'upload échoue
                $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                return;
            }

            // Mettre à jour la propriété image de l'entité Post
            $post->setImage($newFilename);
        }
    }
}

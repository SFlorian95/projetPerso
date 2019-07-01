<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AddType;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class SuperAdminController extends AbstractController
{
    /**
     * @Route("/admin/users", name="super_admin.index")
     */
    public function index(UserRepository $userRepository): Response
    {
        $results = $userRepository->findAll();
        
        return $this->render('super_admin/index.html.twig', [
            'results'=>$results
        ]);
    }
    
    //Route ADD
    /**
     * @Route("/admin/users/form", name="super_admin.form") 
     */
     public function form(Request $request, ObjectManager $objectManager, int $id=null, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AppAuthenticator $authenticator):Response
     {
         // préparation des paramètres du formulaire: $entity et $type
         $entity = $id ? $userRepository->find($id) : new User();
         
         $type = AddType::class;
                       
         // création du formulaire
         $form = $this->createForm($type, $entity);
         $form->handleRequest($request);
         
         // formulaire valide
         if($form->isSubmitted() && $form->isValid()){
          
             // encode the password
            $entity->setPassword(
                $passwordEncoder->encodePassword(
                    $entity,
                    $form->get('password')->getData()
                )
            );
                    
             // mise à jour de la base
            $objectManager->persist($entity);
            $objectManager->flush();
            
           

            // redirectToRoute: redirection
            return $this->redirectToRoute('super_admin.index');
         }
         
         return $this->render('super_admin/form.html.twig', ['form' => $form->createView()]);
     }
     
     //Route UPDATE
     /**
      * @Route("/admin/users/update/{id}", name="super_admin.update")
      * 
      */
     public function update(Request $request, ObjectManager $objectManager, int $id, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AppAuthenticator $authenticator):Response
     { 
         
         $entity = $id ? $userRepository->find($id) : new User();
         
         $type = EditUserType::class;
                       
         // création du formulaire
         $editForm = $this->createForm($type, $entity);
         $editForm->handleRequest($request);
         
         // formulaire valide
         if($editForm->isSubmitted() && $editForm->isValid()){
          
          
                    
             // mise à jour de la base
            $objectManager->persist($entity);
            $objectManager->flush();
            
           

            // redirectToRoute: redirection
            return $this->redirectToRoute('super_admin.index');
         }
         
         return $this->render('super_admin/update.html.twig', ['editForm' => $editForm->createView()]);
     }
     
    // ROUTE DELETE
    /**
     * @Route("/admin/users/delete/{id}", name="super_admin.delete")
     */

    public function delete(int $id, UserRepository $userRepository, ObjectManager $objectManager):Response
    {

        //sélection de l'entité par son identifiant
        $entity = $userRepository->find($id);

        //suppression de l'entité
        $objectManager->remove($entity);
        $objectManager->flush();



        // redirectToRoute: redirection
        return $this->redirectToRoute('super_admin.index');
    }
}

<?php

namespace App\Controller\User;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    
    /**
     * @Route("/user/", name="user.index")
     */
    public function index(): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        //dd($user);
        return $this->render('user/index.html.twig',[
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/user/profile/", name="user.profile")
     * 
     */
    public function profile(): Response
    {
        $result = $this->get('security.token_storage')->getToken()->getUser()->getProfile();
        //dd($result);
        if(empty($result)){
            return $this->redirectToRoute('user.create_profile');
        }
        return $this->render('user/profile.html.twig',[
            'result'=>$result
        ]);
    }
    
    /**
     * @Route("/user/create_profile/{id}", name="user.create_profile")
     * 
     */
    public function createProfile(Request $request, ObjectManager $objectManager, int $id=null, UserRepository $userRepository): Response
    {
       $id = $this->get('security.token_storage')->getToken()->getUser()->getId();
       $entity = new Profile();
       
       $type = ProfileType::class;
       
       //création du formulaire
       $profileForm = $this->createForm($type, $entity);
       $profileForm->handleRequest($request);
       
       $user = $userRepository->find($id);
       $entity->setUser($user);
       
       
        // formulaire valide
         if($profileForm->isSubmitted() && $profileForm->isValid()){
                         
             // mise à jour de la base
            $objectManager->persist($entity);
            $objectManager->flush();
                     
            $user = $userRepository->find($id);
            // redirectToRoute: redirection
            return $this->redirectToRoute('user.index');
         }
         
         return $this->render('user/createForm.html.twig', [
             'profileForm' => $profileForm->createView(),
            ]);
         
    }
    
    /**
     * @Route("/user/profile/{id}/update_profile", name="user.update_profile")
     */
    public function updateProfile(Request $request, ObjectManager $objectManager, int $id, ProfileRepository $profileRepository) :Response
    {
        $entity = $id ? $profileRepository->find($id) : new Profile();
        $type = ProfileType::class;
        
        // création du formulaire
         $profileForm = $this->createForm($type, $entity);
         $profileForm->handleRequest($request);
         
        // formulaire valide
         if($profileForm->isSubmitted() && $profileForm->isValid()){
          
          
                    
             // mise à jour de la base
            $objectManager->persist($entity);
            $objectManager->flush();
            
           

            // redirectToRoute: redirection
            return $this->redirectToRoute('user.profile');
         }
         
         return $this->render('user/createForm.html.twig', [
             'profileForm' => $profileForm->createView(),
            ]);
    }
}

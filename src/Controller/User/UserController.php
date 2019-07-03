<?php

namespace App\Controller\User;

use App\Entity\Comment;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use App\Service\FileService;
use App\Service\StringService;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $user = $this->get('security.token_storage')->getToken()->getUser()->getProfile();
             if(empty($user)){
            return $this->redirectToRoute('user.create_profile');
        }
        //dd($user);
        return $this->render('user/index.html.twig',[
            'user' => $user
        ]);
    }
    
    /**
     * @Route("/user/comment/add/", name="user.add_comment")
     */
    public function addComment(Request $request, ProfileRepository $profileRepository, ObjectManager $objectManager, int $id=null): JsonResponse
    {
        /*
        * récupération de $_POST : propriété request de la requête 
        * récupération à partir de l'attribut name du champ 
        */       
        $content = $request->request->get('content');
        $id = $request->request->get('id');
        //dd($content, $id);
        
        /*
         * insertion du commentaie dans la table
         *  pour enregistrer une entrée dans une table, avec doctrine, il faut créer une instance d'une entitée et utiliser les setters
         */      
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setDatetime(new DateTime() );
        
        /*
         * avec doctrine, pour relier des entités, il faut des instances d'entités
         */
        $profile = $profileRepository->find($id);
         
        // associer une entité à une autre entité : utiliser une entité dans une méthode de l'autre entité 
        $comment->setProfile($profile);
         
        //enregistement de la table
        $objectManager->persist($comment);
        $objectManager->flush();
        
        /*
         * reponse http en json 
         * réexecuter la requête pour récupérer les derniers commentaires
         * json_encode: par défaut, ne converti pas en json les objets
         * obligatoire de renvoyer des arrays
         * toArray : methode de doctrine qui permet de transformer des listes d'entités en array  
         */
        $profile = $profileRepository->find($id);
        $response = new JsonResponse($profile->getComments()->toArray() );
        
        //dd($product->getComments()->toArray() );
        
        return $response;
        
        //dd($comment);
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
    public function createProfile(Request $request, ObjectManager $objectManager, int $id=null, 
    UserRepository $userRepository,StringService $stringService, FileService $fileService): Response
    {
       $id = $this->get('security.token_storage')->getToken()->getUser()->getId();
       $entity = new Profile();
       
       $type = ProfileType::class;
       
        //ajout d'une propriété dynamique (lors de l'execution) pour stocker
        $entity->prevImage = $entity->getImage();
        
       //création du formulaire
       $profileForm = $this->createForm($type, $entity);
       $profileForm->handleRequest($request);
       
       $user = $userRepository->find($id);
       $entity->setUser($user);
       
       
        // formulaire valide
         if($profileForm->isSubmitted() && $profileForm->isValid()){
             
             if(!$entity->getId()){
                 $imageName = $stringService->generateToken(16);
                 //dd($imageName);
                 $uploadedFile = $entity->getImage(); // renvoie un objet UploadedFile
                 $extension = $fileService->getExtension($uploadedFile);
                 $fileService->upload($uploadedFile, 'img/', "$imageName.$extension");
                 
                 // mise à jour de la propriété image 
                 $entity->setImage("$imageName.$extension");     
                //dd($entity,$extension);
             }
                        
             
             // mise à jour de la base
            $objectManager->persist($entity);
            $objectManager->flush();
                     
            $user = $userRepository->find($id);
            // redirectToRoute: redirection
            return $this->redirectToRoute('user.index');
         }
         //dd($user);
         return $this->render('user/createForm.html.twig', [
             'profileForm' => $profileForm->createView(),
             'user' => $user
            ]);
         
    }
    
    /**
     * @Route("/user/profile/{id}/update_profile", name="user.update_profile")
     */
    public function updateProfile(Request $request, ObjectManager $objectManager, int $id, 
    ProfileRepository $profileRepository,UserRepository $userRepository, StringService $stringService, FileService $fileService ) :Response
    {
        $entity = $id ? $profileRepository->find($id) : new Profile();
        $type = ProfileType::class;
        
        $entity->prevImage = $entity->getImage();
        
        // création du formulaire
         $profileForm = $this->createForm($type, $entity);
         $profileForm->handleRequest($request);
         
         $user = $userRepository->find($id);
         
        // formulaire valide
         if($profileForm->isSubmitted() && $profileForm->isValid()){
          
            //si l'entité est mise à jour et qu'une image n'a pas été sélectionné          
            if($entity->getId() && !$entity->getImage()){
                //récupération de la propriété dynamique prevImage pour remplir la propriété image
                $entity->setImage($entity->prevImage);
                
            }
            
            // si l'identifiant est mise a jour et qu'une image a été séléctionné 
            elseif($entity->getId() && $entity->getImage()){
                //unlink : suppression de l'ancienne image
                //avant la création des services : unlink("img/{$entity->prevImage}");
                 $fileService->delete('img', $entity->prevImage);
                 
                /*
                 * transfert de la nouvelle image   
                 * 
                 * avant la création d'un service
                 *   $imageName = bin2hex(random_bytes(16));          
                $uploadedFile = $entity->getImage(); // renvoie un objet UploadedFile
                $extension = $uploadedFile->guessExtension();
                $uploadedFile->move('img/', "$imageName.$extension");              
                 $entity->setImage("$imageName.$extension");          
                 */
                $imageName = $stringService->generateToken(16);          
                $uploadedFile = $entity->getImage(); // renvoie un objet UploadedFile
                $extension = $fileService->getExtension($uploadedFile);
                $fileService->upload($uploadedFile, 'img/', "$imageName.$extension");
                
                 $entity->setImage("$imageName.$extension");                          
            }
                    
             // mise à jour de la base
            $objectManager->persist($entity);
            $objectManager->flush();
            
           

            // redirectToRoute: redirection
            return $this->redirectToRoute('user.profile');
         }
         
         return $this->render('user/createForm.html.twig', [
             'profileForm' => $profileForm->createView(),
             'user' => $user
            ]);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'app_register')]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $users = $entityManager->getRepository(User::class);

        $contentRaw = $request->getContent();
        $content = json_decode($contentRaw);

        $dataEmail = $content -> email;
        $checkedUser = $users->findOneBy(array('email' => $dataEmail));
        
        if ($checkedUser) {
            $rawResponse['response'] = 'user istnieje';

        } else {

            if ($content->password == $content->passwordRepeated) {

                if (strlen($content->password) > 6) {

                    if (preg_match('#[0-9]#',$content->password) && preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/',$content->password)) {
                        $newUser = new User();
                        $newUser->setEmail($dataEmail);

                        $hashedPassword = $passwordHasher->hashPassword(
                            $newUser,
                            $content->password
                        );
                        $newUser->setPassword($hashedPassword);

                        $entityManager->persist($newUser);
                        $entityManager->flush();

                        $rawResponse['response'] = 'jest ok';

                    } else {
                        $rawResponse['response'] = 'haslo musi zawierać przynajmniej jedną cyfrę i znak specjalny'; 
                    }

                } else {
                    $rawResponse['response'] = 'hasło jest za krtótkie'; 
                }

            } else {
                $rawResponse['response'] = 'hasla nie sa takie same';
            }
        
        }

        $response = json_encode($rawResponse);
        return new Response($response);
        // return $this->json([
        //     'recived data'  => $response['response'],
        //   ]);
    }
}

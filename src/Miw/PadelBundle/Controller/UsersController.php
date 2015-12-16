<?php

namespace Miw\PadelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Users controller.
 *
 */
class UsersController extends Controller
{

    /**
     * Lists all Users entities.
     *
     */
    public function indexAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('MiwPadelBundle:Users')->findAll();

        return new Response(
            $serializer->serialize($users, 'json'),
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }
    
    public function validJson ($json) { // checks if all required fields are in
        return
            array_key_exists('username', $json) &&
            array_key_exists('email', $json) &&
            array_key_exists('enabled', $json) &&
            array_key_exists('salt', $json) &&
            array_key_exists('password', $json) &&
            array_key_exists('locked', $json) &&
            array_key_exists('expired', $json) &&
            array_key_exists('roles', $json) &&
            array_key_exists('credentials_expired', $json);
    }
    
    /**
     * Creates a new Users entity.
     *
     */
    public function createAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        if (!$this->validJson(json_decode($request->getContent()))) {
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'Error',
                    'message' => 'Invalid user data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $user = $serializer->deserialize(
                $request->getContent(),
                'Miw\PadelBundle\Entity\Users',
                'json');
        
        $user->setUsernameCanonical(strtolower($user->getUsername()));
        $user->setEmailCanonical(strtolower($user->getEmail()));
        
        $em = $this->getDoctrine()->getManager();
        
        $user_exists = $em->getRepository('Miw\PadelBundle\Entity\Users')
                          ->findOneBy(array('username' => $user->getUsername()));
        
        if (!$user_exists) {
            $em->persist($user);
            $em->flush();
            
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'OK',
                    'user_id' => $user->getId()), 'json'),
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );  
        } else {
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'Error',
                    'message' =>  'User already exists'), 'json'),
                Response::HTTP_CONFLICT,
                array('content-type' => 'application/json')
            );
        }
    }

    /**
     * Finds and displays a Users entity.
     *
     */
    public function showAction($id, $format)
    {
        $content = null;
        $status = null;
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('MiwPadelBundle:Users')->find($id);

        if (!$user) {
            $content = array(
                'result'  => 'NOT FOUND',
                'user_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $content = $user;
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, $format),
            $status,
            array('content-type' => 'application/' . $format)
        );
    }

    /**
     * Edits an existing Users entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $content = null;
        $status = null;
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        if (!$this->validJson(json_decode($request->getContent()))) {
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'Error',
                    'message' => 'Invalid user data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $user_data = $serializer->deserialize(
                $request->getContent(),
                'Miw\PadelBundle\Entity\Users',
                'json');
        
        if ($user_data != null) { // valid JSON
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('MiwPadelBundle:Users')->find($id);
            
            if (!$user) {
                $content = array(
                    'result'  => 'NOT FOUND',
                    'user_id' => $id);
                $status = Response::HTTP_NOT_FOUND;
            } else {
                $user->setUsername($user_data->getUsername());
                $user->setUsernameCanonical($user_data->getUsernameCanonical());
                $user->setEmail($user_data->getEmail());
                $user->setEmailCanonical($user_data->getEmailCanonical());
                $user->setEnabled($user_data->getEnabled());
                $user->setSalt($user_data->getSalt());
                $user->setPassword($user_data->getPassword());
                $user->setLastLogin($user_data->getLastLogin());
                $user->setLocked($user_data->getLocked());
                $user->setExpired($user_data->getExpired());
                $user->setExpiresAt($user_data->getExpiresAt());
                $user->setConfirmationToken($user_data->getConfirmationToken());
                $user->setPasswordRequestedAt($user_data->getPasswordRequestedAt());
                $user->setRoles($user_data->getRoles());
                $user->setCredentialsExpired($user_data->getCredentialsExpired());
                $user->setCredentialsExpireAt($user_data->getCredentialsExpireAt());
                
                $em->flush();

                $content = $user;
                $status = Response::HTTP_OK;
            }
        }
        
        return new Response(
            $serializer->serialize($content, 'json'),
            $status,
            array('content-type' => 'application/json')
        );
    }
    /**
     * Deletes a Users entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $content = null;
        $status = null;
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('MiwPadelBundle:Users')->find($id);

        if (!$user) {
            $content = array(
                'result'  => 'NOT FOUND',
                'user_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $em->remove($user);
            $em->flush();
            
            $content = array(
                'result'  => 'OK',
                'user_id' => $id);
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, 'json'),
            $status,
            array('content-type' => 'application/json')
        );
    }
    
    public function addToGroupAction(Request $request, $id, $groupId) {
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $em->find('MiwPadelBundle:Users', $id);
        $group = $em->find('MiwPadelBundle:Groups', $groupId);
        
        if ($user && $group) {
            $group->addUser($user);
            $em->flush();
            
            $content = $user;
            $status = Response::HTTP_OK;
        } else {
            $content = array(
                'result'  => 'NOT FOUND',
                'message' => 'user or group does not exist');
            $status = Response::HTTP_NOT_FOUND;
        }
        
        return new Response(
            $serializer->serialize($content, 'json'),
            $status,
            array('content-type' => 'application/json')
        );
    }
}

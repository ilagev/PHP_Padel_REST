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
 * Groups controller.
 *
 */
class GroupsController extends Controller
{

    /**
     * Lists all Groups entities.
     *
     */
    public function indexAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        $groups = $em->getRepository('MiwPadelBundle:Groups')->findAll();

        return new Response(
            $serializer->serialize($groups, 'json'),
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }
    
    public function validJson ($json) { // checks if all required fields are in
        return
            array_key_exists('name', $json) &&
            array_key_exists('roles', $json);
    }
    
    /**
     * Creates a new Groups entity.
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
                    'message' => 'Invalid group data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $group = $serializer->deserialize(
                $request->getContent(),
                'Miw\PadelBundle\Entity\Groups',
                'json');
        
        $em = $this->getDoctrine()->getManager();
        
        $group_exists = $em->getRepository('Miw\PadelBundle\Entity\Groups')
                           ->findOneBy(array('name' => $group->getName()));
        
        if (!$group_exists) {
            $em->persist($group);
            $em->flush();
            
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'OK',
                    'group_id' => $group->getId()), 'json'),
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );  
        } else {
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'Error',
                    'message' =>  'Group already exists'), 'json'),
                Response::HTTP_CONFLICT,
                array('content-type' => 'application/json')
            );
        }
    }

    /**
     * Finds and displays a Groups entity.
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
        $group = $em->getRepository('MiwPadelBundle:Groups')->find($id);

        if (!$group) {
            $content = array(
                'result'  => 'NOT FOUND',
                'group_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $content = $group;
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, $format),
            $status,
            array('content-type' => 'application/' . $format)
        );
    }

    /**
     * Edits an existing Groups entity.
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
                    'message' => 'Invalid group data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $group_data = $serializer->deserialize(
                $request->getContent(),
                'Miw\PadelBundle\Entity\Groups',
                'json');
        
        if ($group_data != null) { // valid JSON
            $em = $this->getDoctrine()->getManager();
            $group = $em->getRepository('MiwPadelBundle:Groups')->find($id);
            
            if (!$group) {
                $content = array(
                    'result'  => 'NOT FOUND',
                    'group_id' => $id);
                $status = Response::HTTP_NOT_FOUND;
            } else {
                $group->setName($group_data->getName());
                $group->setRoles($group_data->getRoles());
                
                $em->flush();

                $content = $group;
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
     * Deletes a Groups entity.
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
        $group = $em->getRepository('MiwPadelBundle:Groups')->find($id);

        if (!$group) {
            $content = array(
                'result'  => 'NOT FOUND',
                'group_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $em->remove($group);
            $em->flush();
            
            $content = array(
                'result'  => 'OK',
                'group_id' => $id);
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, 'json'),
            $status,
            array('content-type' => 'application/json')
        );
    }
    
    public function addUserAction(Request $request, $id, $userId) {
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        
        $group = $em->find('MiwPadelBundle:Groups', $id);
        $user = $em->find('MiwPadelBundle:Users', $userId);
        
        if ($user && $group) {
            $group->addUser($user);
            $em->flush();
            
            $content = $group;
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

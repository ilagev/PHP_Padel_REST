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
 * Courts controller.
 *
 */
class CourtsController extends Controller
{

    /**
     * Lists all Courts entities.
     *
     */
    public function indexAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        $courts = $em->getRepository('MiwPadelBundle:Courts')->findAll();

        return new Response(
            $serializer->serialize($courts, 'json'),
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }
    
    public function validJson ($json) { // checks if all required fields are in
        return
            array_key_exists('active', $json);
    }
    
    /**
     * Creates a new Courts entity.
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
                    'result'  => 'Error',
                    'message' => 'Invalid court data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $court = $serializer->deserialize(
                $request->getContent(),
                'Miw\PadelBundle\Entity\Courts',
                'json');
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($court);
        $em->flush();

        return new Response(
            $serializer->serialize(array(
                'result'   => 'OK',
                'group_id' => $court->getId()), 'json'),
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

    /**
     * Finds and displays a Courts entity.
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
        $court = $em->getRepository('MiwPadelBundle:Courts')->find($id);

        if (!$court) {
            $content = array(
                'result'  => 'NOT FOUND',
                'court_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $content = $court;
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, $format),
            $status,
            array('content-type' => 'application/' . $format)
        );
    }
    /**
     * Edits an existing Courts entity.
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
                    'result'  => 'Error',
                    'message' => 'Invalid court data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $court_data = $serializer->deserialize(
                $request->getContent(),
                'Miw\PadelBundle\Entity\Courts',
                'json');
        
        if ($court_data != null) { // valid JSON
            $em = $this->getDoctrine()->getManager();
            $court = $em->getRepository('MiwPadelBundle:Courts')->find($id);
            
            if (!$court) {
                $content = array(
                    'result'  => 'NOT FOUND',
                    'court_id' => $id);
                $status = Response::HTTP_NOT_FOUND;
            } else {
                $court->setActive($court_data->getActive());
                $em->flush();

                $content = $court;
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
     * Deletes a Courts entity.
     *
     */
    public function deleteAction($id)
    {
        $content = null;
        $status = null;
        
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        $court = $em->getRepository('MiwPadelBundle:Courts')->find($id);

        if (!$court) {
            $content = array(
                'result'  => 'NOT FOUND',
                'court_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $em->remove($court);
            $em->flush();
            
            $content = array(
                'result'  => 'OK',
                'court_id' => $id);
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, 'json'),
            $status,
            array('content-type' => 'application/json')
        );
    }
}

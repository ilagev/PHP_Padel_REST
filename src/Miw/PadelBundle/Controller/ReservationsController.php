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
 * Reservations controller.
 *
 */
class ReservationsController extends Controller
{

    /**
     * Lists all Reservations entities.
     *
     */
    public function indexAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        $reservations = $em->getRepository('MiwPadelBundle:Reservations')->findAll();

        return new Response(
            $serializer->serialize($reservations, 'json'),
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }
    
    public function validJson ($json) { // checks if all required fields are in
        return
            array_key_exists('user_id', $json) &&
            array_key_exists('court_id', $json) &&
            array_key_exists('datetime', $json);
    }
    
    /**
     * Creates a new Reservations entity.
     *
     */
    public function createAction(Request $request)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        
        $em = $this->getDoctrine()->getManager();
        
        if (!$this->validJson(json_decode($request->getContent()))) {
            return new Response(
                $serializer->serialize(array(
                    'result'  => 'Error',
                    'message' => 'Invalid reservation data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $data = json_decode($request->getContent());
        $user = $em->find('Miw\PadelBundle\Entity\Users', $data->{'user_id'});
        $court = $em->find('Miw\PadelBundle\Entity\Courts', $data->{'court_id'});
        $datetime = \DateTime::createFromFormat('d/m/Y', $data->{'datetime'});
        
        if (!($user && $court && $datetime)) {
            return new Response(
                $serializer->serialize(array(
                    'result'  => 'Error',
                    'message' => 'User or court does not exist'), 'json'),
                Response::HTTP_NOT_FOUND,
                array('content-type' => 'application/json'));
        }
        
        $reservation = new \Miw\PadelBundle\Entity\Reservations();
        
        $reservation_exists = $em->getRepository('Miw\PadelBundle\Entity\Reservations')
                                 ->findOneBy(array('user'     => $user,
                                                   'court'    => $court,
                                                   'datetime' => $datetime));
        
        if (!$reservation_exists) { // valid JSON
            $reservation->setUser($user);
            $reservation->setCourt($court);
            $reservation->setDatetime($datetime);
            
            $em->persist($reservation);
            $em->flush();
            
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'OK',
                    'reservation_id' => $reservation->getId()), 'json'),
                Response::HTTP_OK,
                array('content-type' => 'application/json')
            );
        } else {
            return new Response(
                $serializer->serialize(array(
                    'result'   => 'Error',
                    'message' =>  'Reservation already exists'), 'json'),
                Response::HTTP_CONFLICT,
                array('content-type' => 'application/json')
            );
        }
        
    }

    /**
     * Finds and displays a Reservations entity.
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
        $reservation = $em->getRepository('MiwPadelBundle:Reservations')->find($id);

        if (!$reservation) {
            $content = array(
                'result'  => 'NOT FOUND',
                'reservation_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $content = $reservation;
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, $format),
            $status,
            array('content-type' => 'application/' . $format)
        );
    }

    /**
     * Edits an existing Reservations entity.
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
                    'message' => 'Invalid reservation data'), 'json'),
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application/json'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $data = json_decode($request->getContent());
        $user = $em->find('Miw\PadelBundle\Entity\Users', $data->{'user_id'});
        $court = $em->find('Miw\PadelBundle\Entity\Courts', $data->{'court_id'});
        $datetime = \DateTime::createFromFormat('d/m/Y', $data->{'datetime'});
        
        $reservation_data = new \Miw\PadelBundle\Entity\Reservations();
            
        $reservation_data->setUser($user);
        $reservation_data->setCourt($court);
        $reservation_data->setDatetime($datetime);

        $reservation = $em->getRepository('MiwPadelBundle:Reservations')->find($id);

        if (!$reservation) {
            $content = array(
                'result'  => 'NOT FOUND',
                'reservation_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $reservation->setDatetime($reservation_data->getDatetime());
            $reservation->setCourt($reservation_data->getCourt());
            $reservation->setUser($reservation_data->getUser());

            $em->flush();

            $content = $reservation;
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, 'json'),
            $status,
            array('content-type' => 'application/json')
        );
    }
    /**
     * Deletes a Reservations entity.
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
        $reservation = $em->getRepository('MiwPadelBundle:Reservations')->find($id);

        if (!$reservation) {
            $content = array(
                'result'  => 'NOT FOUND',
                'reservation_id' => $id);
            $status = Response::HTTP_NOT_FOUND;
        } else {
            $em->remove($reservation);
            $em->flush();
            
            $content = array(
                'result'  => 'OK',
                'reservation_id' => $id);
            $status = Response::HTTP_OK;
        }
        
        return new Response(
            $serializer->serialize($content, 'json'),
            $status,
            array('content-type' => 'application/json')
        );
    }
}

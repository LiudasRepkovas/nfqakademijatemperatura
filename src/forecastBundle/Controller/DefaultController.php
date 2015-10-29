<?php

namespace forecastBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Forecast\Forecast;
use Geocoder\Geocoder;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }
    /**
     * @Route("forecast/")
     * @Template()
     */
    public function forecastAction(Request $request){
        $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
        $geocoder = new \Geocoder\Provider\GoogleMaps($curl);
        $forecast = new Forecast('bd15241cebaaeba53a627a637677549a');

        $data = array(
            'address' => 'Konstitucijos pr. 7, LT-09308 Vilnius'
            );
        $form = $this->createFormBuilder($data)
            ->add('address', 'text', array('label' => 'Įveskite adresą:'))
            ->add('save', 'submit', array('label' => 'Rodyti temperatūrą'))
            ->getForm();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
        }

        $geo = $geocoder->geocode($data['address']);
        $foundAddress = $geo->first();
        $temperature = $forecast->get(
            $foundAddress->getLatitude(),
            $foundAddress->getLongitude(),
            null,
            array(
            'units' => 'si',
            'exclude' => 'flags'
            )
        );
        return array(
            'temperature' =>  $temperature->currently->temperature,
            'foundAddress' => $foundAddress,
            'form' => $form->createView()
        );

    }
}

<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 31/10/16
 * Time: 08:41
 */

namespace LocationBundle\Command;


use LocationBundle\Entity\Country;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertAddressCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('address:convert')

            ->setDescription('Convert a address county to address geocoding')

            ->setHelp("This command allows you to create users...")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');


        $address = $em->getRepository('LocationBundle:Address')->findByFormatedAddress(null);

        $output->writeln([
            '==================',
            'Address converting',
            '==================',
        ]);

        foreach($address as $a) {

            if ($a->getCity()) {
                $formatedAddress = $a->getStreet() . ' ' . $a->getCity()->getZipCode() . ' ' . $a->getCity()->getName();
            }
            $output->writeln('Address: ' . $formatedAddress);



            $formatedAddressGoogle = str_replace (" ", "+", urlencode($formatedAddress));

            $results = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . $formatedAddressGoogle . '&sensor=false');

            $results = json_decode($results, true);


            if (isset($results['results'][0])) {


                $add = $results['results'][0];

                foreach ($add['address_components'] as $comp) {

                    if (in_array('country', $comp['types'])) {
                        $country = $em->getRepository('LocationBundle:Country')->findOneBy([
                            'name' => $comp['long_name'],
                            'code' => $comp['short_name']
                        ]);

                        if (!$country) {

                            $country = new Country();

                            $country->setCode($comp['short_name']);
                            $country->setName($comp['long_name']);

                            $em->persist($country);
                            $em->flush($country);

                        }

                        $a->setCountry($country);


                    }


                }

                $a->setFormatedAddress($add['formatted_address']);
                $a->setJsonGoogle(json_encode($add));
                $a->setGeoLatitude($add['geometry']['location']['lat']);
                $a->setGeoLongitude($add['geometry']['location']['lng']);

                $em->persist($a);

                $output->writeln('Address: NOK');
            } else {
                $output->writeln('Address: OK');
            }


        }


        $em->flush();

    }

}
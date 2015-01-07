<?php

namespace Uff\CalculatorBundle\Controller;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Security\Acl\Exception\Exception;
use Uff\CalculatorBundle\Entity\Environment;
use Uff\CalculatorBundle\Entity\Instance;
use Uff\CalculatorBundle\Form\EnvironmentType;

/**
 * Environment controller.
 *
 */
class EnvironmentController extends Controller
{

    /**
     * Lists all Environment entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UffCalculatorBundle:Environment')->findAll();

        return $this->render('UffCalculatorBundle:Environment:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Environment entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Environment();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('environment_choose_instances', array('id' => $entity->getId())));
        }

        return $this->render('UffCalculatorBundle:Environment:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Environment entity.
    *
    * @param Environment $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Environment $entity)
    {
        $form = $this->createForm(new EnvironmentType(), $entity, array(
            'action' => $this->generateUrl('environment_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Environment entity.
     *
     */
    public function newAction()
    {
        $entity = new Environment();
        $form   = $this->createCreateForm($entity);

        return $this->render('UffCalculatorBundle:Environment:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Environment entity.
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Environment entity.');
        }

        $instances = $entity->getInstances();
        $providers = $entity->getProviders();

        $deleteForm = $this->createDeleteForm($id);

        $heuristic_input = $this->renderView('UffCalculatorBundle:Environment:heuristic_input.txt.twig', array(
            'entity'      => $entity,
            'instances'   => $instances,
            'providers'   => $providers
        ));

//        echo $heuristic_input;
        //die();

        # config
        $filesystem = new Filesystem();
        $heuristic_dir = __DIR__.'/../../../../heuristic/';
        $heuristic_filename = 'GraspCC-fed';
        $input_dir = '/tmp/';
        $input_filename = sha1(time().microtime(true)).'.txt';

        # write input text file
        $filesystem->dumpFile($input_dir.$input_filename, $heuristic_input);

        # execute de heuristic
        $cmd = $heuristic_dir.$heuristic_filename.' '.$input_dir.$input_filename.' 0.5 0.5';
        $return = exec($cmd, $output, $return_var);

        # parse output
        $parsed_output = $this->parseHeuristicOutput($output);

        # delete the input file
        $filesystem->remove($input_dir.$input_filename);

        #
        $instances = $this->parseAllResultData($instances, $parsed_output);

        return $this->render('UffCalculatorBundle:Environment:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'instances'   => $instances,
            'output'      => $parsed_output
        ));
    }

    /**
     * @return mixed
     */
    private function getAWSPricing()
    {
        $memcache = $this->getMemCache();

        if ($pricing = $memcache->get('aws_ec2_instances_pricing'))
        {
            return $pricing;
        }

        $subject = file_get_contents('http://a0.awsstatic.com/pricing/1.0.19/ec2/linux-od.min.js');
        $pattern = '/callback\((.+)\);/';
        preg_match($pattern, $subject, $matches);
        $pricing = $matches[1];
        $pricing = str_replace(',', ',"', $pricing);
        $pricing = str_replace(':', '":', $pricing);
        $pricing = str_replace('{', '{"', $pricing);
        $pricing = str_replace('""', '"', $pricing);
        $pricing = str_replace(',"{', ',{', $pricing);
        $pricing = json_decode($pricing);
        $pricing = $pricing->config->regions[7]->instanceTypes;

        $memcache->set('aws_ec2_instances_pricing', $pricing, false, 86400) or die ("Failed to save data at the server");

        return $pricing;
    }

    /**
     * @return array|string
     */
    private function getAzurePricing()
    {
        // conecta no cache
        $memcache = $this->getMemCache();

        // verifica se já existe no cache
        if ($pricing = $memcache->get('azure_pricing'))
        {
            return $pricing;
        }

        // caso nao exista faz o fetch
        $json_pricing = file_get_contents('https://www.parsehub.com/api/scrapejob/dl?api_key=tPWNsl1yTOOoXWcYuZ0dVBkVWZuhok9r&run_token=t6IxCewQa2S9vv5yNNP-XToP_5o4hHJn&format=json&raw=1');

        // faz o decode do json
        $pricing = json_decode($json_pricing);

        // armazena no cache
        $memcache->set('azure_pricing', $pricing->instances, false, 86400) or die ("Failed to save data at the server");

        // retorna pricing
        return $pricing->instances;
    }

    /**
     * @return array|string
     */
    private function getGooglePricing()
    {
        // conecta no cache
        $memcache = $this->getMemCache();

        // verifica se já existe no cache
        if ($pricing = $memcache->get('google_pricing'))
        {
            return $pricing;
        }

        // caso nao exista faz o fetch
        $json_pricing = file_get_contents('https://www.parsehub.com/api/scrapejob/dl?api_key=tPWNsl1yTOOoXWcYuZ0dVBkVWZuhok9r&run_token=tCFRfqrYjbKdLwCg_rtbcGQ3m8GmJMwf&format=json&raw=1');

        // faz o decode do json
        $pricing = json_decode($json_pricing);

        // armazena no cache
        $memcache->set('google_pricing', $pricing->instance_types, false, 86400) or die ("Failed to save data at the server");

        return $pricing->instance_types;
    }

    /**
     * @return \Memcache
     */
    private function getMemCache()
    {
        $memcache = new \Memcache;
        $memcache->connect('localhost', 11211) or die ("Could not connect");

        return $memcache;
    }

    /**
     * @param $size
     * @return float
     * @throws \Symfony\Component\Security\Acl\Exception\Exception
     */
    private function getGflopsByInstanceSize($size)
    {
        switch ($size)
        {
            case 't1.micro': return 19.2;
            case 'm1.small': return 19.2;
            case 'm3.medium': return 19.2;
            case 'm3.large': return 38.4;
            case 'm3.xlarge': return 76.8;
            case 'm3.2xlarge': return 153.6;
            default: throw new Exception(sprintf('Unable to find gflops to size "%s".', $size));
        }
    }

    /**
     * @param $storageGB
     * @return int
     */
    private function getDiskByStorageGB($storageGB)
    {
        preg_match('/(\d+) x (\d+)/', $storageGB, $matches);

        if (array_key_exists(1, $matches) && array_key_exists(2, $matches))
        {
            return $matches[1] * $matches[2];
        }
        else
        {
            return 0;
            //throw new Exception(sprintf('Unable to calculate storage with "%s".', $storageGB));
        }
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function chooseInstancesAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);
        $aws_ec2_instances = $this->getAWSPricing();
        $azure_pricing = $this->getAzurePricing();
        $google_pricing = $this->getGooglePricing();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Environment entity.');
        }

        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST')
        {
            $selected_instances = $request->request->get('instances');

            // delete previous instances
            $instances = $em->getRepository('UffCalculatorBundle:Instance')->findBy(array('environment' => $id));
            if (count($instances) > 0) foreach ($instances as $instance)
            {
                $em->remove($instance);
                $em->flush();
            }

            // delete previous instance providers
            $providers = $em->getRepository('UffCalculatorBundle:EnvironmentProvider')->findBy(array('environment' => $id));
            if (count($providers) > 0) foreach ($providers as $provider)
            {
                $em->remove($provider);
                $em->flush();
            }

            // create aws new instances
            if (array_key_exists('aws', $selected_instances))
            {
                $this->createAmazonInstances($em, $entity, $selected_instances['aws'], $aws_ec2_instances);
            }

            // create new azure instances
            if (array_key_exists('azure', $selected_instances))
            {
                $this->createAzureInstances($em, $entity, $selected_instances['azure'], $azure_pricing);
            }

            // create new google instances
            if (array_key_exists('google', $selected_instances))
            {
                $this->createGoogleInstances($em, $entity, $selected_instances['google'], $google_pricing);
            }

            return $this->redirect($this->generateUrl('environment_show', array('id' => $entity->getId())));
        }
        else
        {
            return $this->render('UffCalculatorBundle:Environment:choose_instances.html.twig', array(
                'entity' => $entity,
                'aws_ec2_instances' => $aws_ec2_instances,
                'azure_instances' => $azure_pricing,
                'google_instances' => $google_pricing
            ));
        }
    }

    /**
     * @param $em
     * @param $entity
     * @param $selected_instances
     * @param $aws_ec2_instances
     */
    private function createAmazonInstances($em, $entity, $selected_instances, $aws_ec2_instances)
    {
        // cria entrada na tabela de associação entre provider e environment
        $environmentProvider = new \Uff\CalculatorBundle\Entity\EnvironmentProvider();
        $environmentProvider->setName("aws");
        $environmentProvider->setEnvironment($entity);

        foreach ($aws_ec2_instances as $instance_type)
        {
            foreach ($instance_type->sizes as $instance_size)
            {
                if (in_array($instance_size->size, $selected_instances))
                {
                    $instance = new Instance();
                    $instance->setName($instance_size->size);
                    $instance->setRam($instance_size->memoryGiB);
                    $instance->setPrice($instance_size->valueColumns[0]->prices->USD);
                    $instance->setGflops($this->getGflopsByInstanceSize($instance_size->size));
                    $instance->setPlataform(64);
                    $instance->setEnvironment($entity);
                    $instance->setDisk($this->getDiskByStorageGB($instance_size->storageGB));
                    $instance->setQuantity(0);
                    $instance->setProvider("aws");

                    $environmentProvider->incrementInstanceCount();

                    $em->persist($instance);
                }
            }
        }

        $em->persist($environmentProvider);
        $em->flush();
    }

    /**
     * @param $em
     * @param $entity
     * @param $selected_instances
     * @param $pricing
     */
    private function createAzureInstances($em, $entity, $selected_instances, $pricing)
    {
        // cria entrada na tabela de associação entre provider e environment
        $environmentProvider = new \Uff\CalculatorBundle\Entity\EnvironmentProvider();
        $environmentProvider->setName("azure");
        $environmentProvider->setEnvironment($entity);

        foreach ($pricing as $instance_type)
        {
            if (in_array($instance_type->name, $selected_instances))
            {
                $instance = new Instance();
                $instance->setName($instance_type->name);
                $instance->setRam($instance_type->ram);
                $instance->setPrice($instance_type->price);
                $instance->setGflops($instance_type->cores * 23.464);
                $instance->setPlataform(64);
                $instance->setEnvironment($entity);
                $instance->setDisk($instance_type->disk);
                $instance->setQuantity(0);
                $instance->setProvider("azure");

                $environmentProvider->incrementInstanceCount();

                $em->persist($instance);
            }
        }

        $em->persist($environmentProvider);
        $em->flush();
    }


    /**
     * @param $em
     * @param $entity
     * @param $selected_instances
     * @param $pricing
     */
    private function createGoogleInstances($em, $entity, $selected_instances, $pricing)
    {
        // cria entrada na tabela de associação entre provider e environment
        $environmentProvider = new \Uff\CalculatorBundle\Entity\EnvironmentProvider();
        $environmentProvider->setName("google");
        $environmentProvider->setEnvironment($entity);

        foreach ($pricing as $instance_type)
        {
            if (in_array($instance_type->name, $selected_instances))
            {
                $instance = new Instance();
                $instance->setName($instance_type->name);
                $instance->setRam($instance_type->memory);
                $instance->setPrice($instance_type->typical_price);
                $instance->setGflops($instance_type->virtual_cores * 20.8);
                $instance->setPlataform(64);
                $instance->setEnvironment($entity);
                $instance->setDisk(10); // TODO: disk??!?!
                $instance->setQuantity(0);
                $instance->setProvider("google");

                $environmentProvider->incrementInstanceCount();

                $em->persist($instance);
                $em->flush();
            }
        }

        $em->persist($environmentProvider);
        $em->flush();
    }

    /**
     * @param $output
     * @return array
     */
    private function parseHeuristicOutput($output)
    {
        $parsed = array();
        if (count($output) > 0)
        {
            foreach ($output as $row)
            {
                if (preg_match('/Package \[(\d+)\]/', $row, $matches))
                {
                    $package = $matches[1];
                    $parsed['packages'][$package] = 0;
                }
                elseif (preg_match('/\[\d+\] = \d+/', $row, $matches))
                {
                    $parsed['packages'][$package]++;
                }
                elseif (preg_match('/\[Best\] Maximum time = (.+) hours/', $row, $matches))
                {
                    $parsed['best_maximum_time'] = $matches[1];
                }
                elseif (preg_match('/\[Best\] Monetary Cost = \$(.+)/', $row, $matches))
                {
                    $parsed['best_monetary_cost'] = $matches[1];
                }
                elseif (preg_match('/\[Best\] Communication Cost = \$(.+)/', $row, $matches))
                {
                    $parsed['best_communication_cost'] = $matches[1];
                }
            }
        }
        else
        {
            return array(
                'best_maximum_time' => null,
                'best_monetary_cost' => null,
                'best_communication_cost' => null
            );
        }

//        print_r(array());
//        print_r($output);
//        print_r($parsed);
//        die();

        return $parsed;
    }

    /**
     * @param $instances
     * @param $parsed_output
     * @return mixed
     */
    private function parseAllResultData($instances, $parsed_output)
    {
        if (!array_key_exists('packages', $parsed_output)) return $instances;

        foreach ($instances as $package => $instance)
        {
            $instance->setQuantity($parsed_output['packages'][$package]);
        }

        return $instances;
    }

    /**
     * Displays a form to edit an existing Environment entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Environment entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('UffCalculatorBundle:Environment:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Environment entity.
    *
    * @param Environment $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Environment $entity)
    {
        $form = $this->createForm(new EnvironmentType(), $entity, array(
            'action' => $this->generateUrl('environment_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Environment entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Environment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('environment_show', array('id' => $id)));
        }

        return $this->render('UffCalculatorBundle:Environment:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Environment entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Environment entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('environment'));
    }

    /**
     * Creates a form to delete a Environment entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('environment_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

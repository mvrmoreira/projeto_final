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

            return $this->redirect($this->generateUrl('environment_show', array('id' => $entity->getId())));
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
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Environment entity.');
        }

        $instances = $entity->getInstances();

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('UffCalculatorBundle:Environment:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'instances'   => $instances
        ));
    }

    /**
     * @return mixed
     */
    private function getAWSPricing()
    {
        $memcache = new \Memcache;
        $memcache->connect('localhost', 11211) or die ("Could not connect");

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

    public function chooseInstancesAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);
        $aws_ec2_instances = $this->getAWSPricing();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Environment entity.');
        }

        $request = Request::createFromGlobals();

        if ($request->getMethod() == 'POST')
        {
            $instances_quantity = $request->request->get('instances');

            // delete previous instances
            $instances = $em->getRepository('UffCalculatorBundle:Instance')->findBy(array('environment' => $id));
            if (count($instances) > 0) foreach ($instances as $instance)
            {
                $em->remove($instance);
                $em->flush();
            }

            // create new instances
            foreach ($aws_ec2_instances as $instance_type)
            {
                foreach ($instance_type->sizes as $instance_size)
                {
                    $quantity = $instances_quantity[$instance_size->size];

                    if ($quantity > 0)
                    {
                        $instance = new Instance();
                        $instance->setRam($instance_size->memoryGiB);
                        $instance->setPrice($instance_size->valueColumns[0]->prices->USD);
                        $instance->setGflops($this->getGflopsByInstanceSize($instance_size->size));
                        $instance->setPlataform(64);
                        $instance->setEnvironment($entity);
                        $instance->setDisk($this->getDiskByStorageGB($instance_size->storageGB));
                        $instance->setQuantity($quantity);

                        $em->persist($instance);
                        $em->flush();
                    }
                }
            }

            return $this->redirect($this->generateUrl('environment_show', array('id' => $entity->getId())));
        }
        else
        {
            return $this->render('UffCalculatorBundle:Environment:aws_ec2_instances.html.twig', array(
                'entity' => $entity,
                'aws_ec2_instances' => $aws_ec2_instances
            ));
        }
    }

    /**
     * Calculated according to the heuristic
     * TODO: refact the execute of heuristic
     */
    public function calculateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Environment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Environment entity.');
        }

        $instances = $entity->getInstances();

        $heuristic_input = $this->renderView('UffCalculatorBundle:Environment:heuristic_input.txt.twig', array(
            'entity'      => $entity,
            'instances'   => $instances
        ));

        # config
        $filesystem = new Filesystem();
        $heuristic_dir = __DIR__.'/../../../../heuristic/';
        $heuristic_filename = 'GraspCC';
        $input_dir = '/tmp/';
        $input_filename = sha1(time().microtime(true)).'.txt';

        # write input text file
        $filesystem->dumpFile($input_dir.$input_filename, $heuristic_input);

        # execute de heuristic
        $cmd = $heuristic_dir.$heuristic_filename.' '.$input_dir.$input_filename.' 0.5 0.5';
        $return = exec($cmd, $output, $return_var);

        # delete the input file
        $filesystem->remove($input_dir.$input_filename);

        # show output
        return $this->render('UffCalculatorBundle:Environment:heuristic_output.html.twig', array(
            'entity'      => $entity,
            'instances'   => $instances,
            'output'      => $output
        ));
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

            return $this->redirect($this->generateUrl('environment_edit', array('id' => $id)));
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

<?php

namespace Uff\CalculatorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Uff\CalculatorBundle\Entity\Instance;
use Uff\CalculatorBundle\Form\InstanceType;

/**
 * Instance controller.
 *
 */
class InstanceController extends Controller
{

    /**
     * Lists all Instance entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UffCalculatorBundle:Instance')->findAll();

        return $this->render('UffCalculatorBundle:Instance:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Instance entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Instance();
        $awsPricing = $this->getAWSPricing();
        $form = $this->createCreateForm($entity, $awsPricing);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('instance_show', array('id' => $entity->getId())));
        }

        return $this->render('UffCalculatorBundle:Instance:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'awsPricing' => $awsPricing,
        ));
    }

    /**
     * @return mixed
     */
    private function getAWSPricing()
    {
        $subject = file_get_contents('http://a0.awsstatic.com/pricing/1/ec2/linux-od.min.js');
        $pattern = '/callback\((.+)\);/';
        preg_match($pattern, $subject, $matches);
        $pricing = $matches[1];
        $pricing = str_replace(',', ',"', $pricing);
        $pricing = str_replace(':', '":', $pricing);
        $pricing = str_replace('{', '{"', $pricing);
        $pricing = str_replace('""', '"', $pricing);
        $pricing = str_replace(',"{', ',{', $pricing);
        $pricing = json_decode($pricing);

        return $pricing->config->regions[7]->instanceTypes;
    }

    /**
     * Creates a form to create a Instance entity.
     *
     * @param Instance $entity The entity
     *
     * @param $awsPricing
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Instance $entity, $awsPricing = null)
    {
        $form = $this->createForm(new InstanceType($awsPricing), $entity, array(
            'action' => $this->generateUrl('instance_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Instance entity.
     *
     */
    public function newAction()
    {
        $entity = new Instance();
        $awsPricing = $this->getAWSPricing();
        $form   = $this->createCreateForm($entity, $awsPricing);

        return $this->render('UffCalculatorBundle:Instance:new.html.twig', array(
            'entity' => $entity,
            'awsPricing' => $awsPricing,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Instance entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Instance')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Instance entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('UffCalculatorBundle:Instance:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Instance entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Instance')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Instance entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('UffCalculatorBundle:Instance:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Instance entity.
    *
    * @param Instance $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Instance $entity)
    {
        $form = $this->createForm(new InstanceType(), $entity, array(
            'action' => $this->generateUrl('instance_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Instance entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UffCalculatorBundle:Instance')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Instance entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('instance_edit', array('id' => $id)));
        }

        return $this->render('UffCalculatorBundle:Instance:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Instance entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UffCalculatorBundle:Instance')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Instance entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('instance'));
    }

    /**
     * Creates a form to delete a Instance entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('instance_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

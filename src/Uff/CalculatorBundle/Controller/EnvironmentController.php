<?php

namespace Uff\CalculatorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Uff\CalculatorBundle\Entity\Environment;
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
     * Calculated according to the heuristic
     *
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

        $filesystem = new Filesystem();
        $filesystem->touch('instance.txt');

        echo $heuristic_input; die();
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

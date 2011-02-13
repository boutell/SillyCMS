<?php

namespace Application\SillyCMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Application\SillyCMSBundle\Entity\Page;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\TextareaField;

class PageController extends Controller
{
    protected $page;
    
    protected function pre($slug)
    {
      $em = $this->getEm();
      $this->page = $em->getRepository('SillyCMSBundle:Page')->findOneBy(array('slug' => $slug));
      if (!$this->page)
      {
        $this->page = new Page();
        $this->page->setSlug($slug);
        $this->page->setTitle('New Page');
        $this->page->setBody('Edit this page to give it some content.');
      }
    }
    
    protected function getParam($p, $d = null)
    {
      return $this->get('request')->get($p, $d);
    }
    
    protected function getForm()
    {
      $form = new Form('page', $this->page, $this->get('validator'));
      $form->add(new TextField('title'));
      $form->add(new TextareaField('body'));
      return $form;
    }
    
    protected function getEm()
    {
      return $this->get('doctrine.orm.entity_manager');
    }
    
    /**
     * @Template
     */
    public function indexAction()
    {
      $em = $this->getEm();
      $pages = $em->createQuery('select p from SillyCMSBundle:Page p order by p.title')->getResult();
      return array('pages' => $pages);
    }

    /**
     * @Template
     */
    public function showAction($slug)
    {
        $this->pre($slug);
        return array('title' => $this->page->getTitle(), 'slug' => $this->page->getSlug(), 'body' => $this->page->getBody());
    }
    
    public function editAction()
    {
        $this->pre($slug);
        $form = $this->getForm();
        return $this->render('SillyCMSBundle:Page:edit.twig.html', array('form' => $form, 'slug' => $slug));
    }
    
    public function saveAction()
    {
        $slug = $this->getParam('slug');
        $this->pre($slug);
        $form = $this->getForm();

        $form->bind($this->get('request')->request->get('page'));

        if ($form->isValid()) 
        {
          $em = $this->getEm();
          $em->persist($this->page);
          $em->flush();
          return $this->redirect($this->generateUrl('show', array('slug' => $slug)));
        }
        return $this->render('SillyCMSBundle:Page:edit.twig.html', array('form' => $form, 'slug' => $slug));
    }
}

<?php

namespace Application\SillyCMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Application\SillyCMSBundle\Entity\Page;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;

class PageController extends Controller
{
    protected $page;
    protected function pre($slug)
    {
      if (!(substr($slug, 0, 1) === '/'))
      {
        // /:slug won't give us that leading /
        $slug = '/' . $slug;
      }
      $em = $this->get('doctrine.orm.entity_manager');
      $this->page = $em->createQuery('SELECT p FROM SillyCMSBundle:Page p WHERE p.slug = ?1')->setParameter(1, $slug)->getSingleResult();
    }
    public function showAction($slug)
    {
        $this->pre($slug);
        return $this->render('SillyCMSBundle:Page:show.twig', array('title' => $this->page->getTitle(), 'slug' => $this->page->getSlug(), 'body' => $this->page->getBody()));
    }
    
    public function editAction($slug)
    {
        $this->pre($slug);
        $form = new Form('page', $this->page, $this->get('validator'));
        $form->add(new TextField('title'));
        $form->add(new TextField('body'));

        return $this->render('SillyCMSBundle:Page:edit.twig', array('form' => $form));
    }
}

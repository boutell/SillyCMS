<?php

namespace Application\SillyCMSBundle\Entity;

/**
 * @orm:Entity
 */
 
class Page
{
   /**
    * @orm:Id
    * @orm:Column(type="integer")
    * @orm:GeneratedValue(strategy="IDENTITY")
    */
   protected $id;
   /**
    * @orm:Column(type="string", length="100")
    * @validation:NotBlank()
    */
   protected $title;
   /**
    * @orm:Column(type="string", length="200")
    */
   protected $slug;
   /**
    * @orm:Column(type="string")
    */
   protected $body;
   
   public function getId()
   {
     return $this->id;
   }
   
   public function setTitle($title)
   {
     $this->title = $title;
   }

   public function setSlug($slug)
   {
     $this->slug = $slug;
   }

   public function setBody($body)
   {
     $this->body = $body;
   }
   
   public function getTitle()
   {
     return $this->title;
   }
   
   public function getSlug()
   {
     return $this->slug;
   }
   
   public function getBody()
   {
     return $this->body;
   }
}

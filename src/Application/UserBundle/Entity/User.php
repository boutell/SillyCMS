<?php

namespace Application\UserBundle\Entity;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @orm:Entity
 */
class User extends BaseUser
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
   protected $username;

   /**
    * @orm:Column(type="string", length="100")
    * @validation:NotBlank()
    */
   protected $usernameCanonical;

   /**
    * @orm:Column(type="string", length="512")
    * @validation:NotBlank()
    */
   protected $password;

   /**
    * @orm:Column(type="string", length="100")
    * @validation:NotBlank()
    */
   protected $email;

   /**
    * @orm:Column(type="string", length="100")
    * @validation:NotBlank()
    */
   protected $emailCanonical;

   /**
    * @orm:Column(type="string", length="100")
    * @validation:NotBlank()
    */
   protected $algorithm;

   /**
    * @orm:Column(type="string", length="100")
    * @validation:NotBlank()
    */
   protected $salt;


   /**
     * @orm:Column(type="boolean", length="1")
     */
    protected $enabled;


   

}

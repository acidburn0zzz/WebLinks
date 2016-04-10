<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 09/04/16
 * Time: 14:54
 */

namespace WebLinks\Domain;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
  /**
   * User id.
   *
   * @var integer
   */
  private $id;

  /**
   * User name.
   *
   * @var varchar
   */
  private $username;

  /**
   * User password.
   *
   * @var varchar
   */
  private $password;

  /**
   * User salt.
   *
   * @var varchar
   */
  private $salt;

  /**
   * User role.
   *
   * @var varchar
   */
  private $role;


  public function getId()
  {
      return $this->id;
  }

  public function setId($id)
  {
      $this->id = $id;
  }

  public function getUsername()
  {
      return $this->username;
  }

  public function setUsername($username)
  {
      $this->username = $username;
  }

  public function getPassword()
  {
      return $this->password;
  }

  public function setPassword($password)
  {
      $this->password = $password;
  }

  public function getSalt()
  {
      return $this->salt;
  }

  public function setSalt($salt)
  {
      $this->salt = $salt;
  }

  public function getRole()
  {
      return $this->role;
  }

  public function setRole($role)
  {
      $this->role = $role;
  }

  /**
   * @inheritDoc
   */
  public function getRoles()
  {
    return array($this->getRole());
  }

  /**
   * @inheritDoc
   */
  public function eraseCredentials() {
    // Nothing to do here
  }

}


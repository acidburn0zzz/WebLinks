<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 09/04/16
 * Time: 15:09
 */

namespace WebLinks\DAO;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use WebLinks\Domain\User;

class UserDAO extends DAO implements UserProviderInterface
{
    /**
     * Returns a list of all user, sorted by id.
     *
     * @return array A list of all users.
     */
    public function findAll() {
        $sql = "SELECT * FROM t_user ORDER BY user_role, user_name";
        $result = $this->getDb()->fetchAll($sql);

        $entities = array();
        foreach ($result as $row) {
            $id = $row['user_id'];
            $entities[$id] = $this->buildDomainObject($row);
        }
        return $entities;
    }

    /**
     * Returns a user matching the supplied id.
     *
     * @param integer $id The user id.
     * @return throws|User an exception if no matching user is found
     * @throws \Exception
     */
    public function find($id) {
        $sql = "SELECT * FROM t_user WHERE user_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No user matching id " . $id);
    }

  /**
   * Saves a user into the database.
   *
   * @param \WebLinks\Domain\User $user The user to save
   */
  public function save(User $user) {
    $userData = array(
      'user_name' => $user->getUsername(),
      'user_salt' => $user->getSalt(),
      'user_password' => $user->getPassword(),
      'user_role' => $user->getRole()
    );

    if ($user->getId()) {
      // The user has already been saved : update it
      $this->getDb()->update('t_user', $userData, array('user_id' => $user->getId()));
    } else {
      // The user has never been saved : insert it
      $this->getDb()->insert('t_user', $userData);
      // Get the id of the newly created user and set it on the entity.
      $id = $this->getDb()->lastInsertId();
      $user->setId($id);
    }
  }

  /**
   * Removes a user from the database.
   *
   * @param @param integer $id The user id.
   */
  public function delete($id) {
    // Delete the user
    $this->getDb()->delete('t_user', array('user_id' => $id));
  }


    /**
     * Creates a User object based on a DB row.
     *
     * @param array $row The DB row containing User data.
     * @return \WebLinks\Domain\User
     */
    protected function buildDomainObject($row) {
        $user = new User();
        $user->setId($row['user_id']);
        $user->setUsername($row['user_name']);
        $user->setPassword($row['user_password']);
        $user->setSalt($row['user_salt']);
        $user->setRole($row['user_role']);
        return $user;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $sql = "SELECT * FROM t_user WHERE user_name=?";
        $row = $this->getDb()->fetchAssoc($sql, array($username));

        if ($row)
        {
            return $this->buildDomainObject($row);
        }
        else
        {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return 'WebLinks\Domain\User' === $class;
    }
}
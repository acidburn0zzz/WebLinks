<?php

namespace WebLinks\DAO;

use WebLinks\Domain\Link;

class LinkDAO extends DAO 
{

  /**
   * @var \WebLinks\DAO\UserDAO
   */
  private $userDAO;

  public function setUserDAO(UserDAO $userDAO) {
    $this->userDAO = $userDAO;
  }


  /**
   * Returns a list of all links, sorted by id.
   *
   * @return array A list of all links.
   */
  public function findAll() {
    $sql = "SELECT * FROM t_link ORDER BY link_id DESC";
    $result = $this->getDb()->fetchAll($sql);

    // Convert query result to an array of domain objects
    $entities = array();
    foreach ($result as $row) {
        $id = $row['link_id'];
        $entities[$id] = $this->buildDomainObject($row);
    }
    return $entities;
  }

  /**
   * Returns a link matching the supplied id.
   *
   * @param integer $id The link id.
   *
   * @return \WebLinks\Domain\Link|throws an exception if no matching link is found
   */
  public function find($id) {
    $sql = "SELECT * FROM t_link WHERE link_id=?";
    $row = $this->getDb()->fetchAssoc($sql, array($id));

    if ($row)
      return $this->buildDomainObject($row);
    else
      throw new \Exception("No link matching id " . $id);
  }

  /**
   * Saves a link into the database.
   *
   * @param \WebLinks\Domain\Link $link The link to save
   */
  public function save(Link $link)
  {
    $linkData = array(
      'link_title' => $link->getTitle(),
      'link_url' => $link->getUrl(),
      'user_id' => $link->getUser()->getId()
    );

    if ($link->getId())
    {
      $this->getDb()->update('t_link', $linkData, array('link_id' => $link->getId()));
    }
    else
    {
      $this->getDb()->insert('t_link', $linkData);
      $id = $this->getDb()->lastInsertId();
      $link->setId($id);
    }
  }

  /**
   * Removes an link from the database.
   *
   * @param integer $id The link id.
   */
  public function delete($id) {
    // Delete the link
    $this->getDb()->delete('t_link', array('link_id' => $id));
  }


  /**
   * Creates an Link object based on a DB row.
   *
   * @param array $row The DB row containing Link data.
   * @return \WebLinks\Domain\Link
   */
  protected function buildDomainObject($row) {
    $link = new Link();
    $link->setId($row['link_id']);
    $link->setUrl($row['link_url']);
    $link->setTitle($row['link_title']);

    if (array_key_exists('user_id', $row)) {
        $userId = $row['user_id'];
        $user = $this->userDAO->find($userId);
        $link->setUser($user);
    }

    return $link;
  }
}

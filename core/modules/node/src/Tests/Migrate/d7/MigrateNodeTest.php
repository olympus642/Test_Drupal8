<?php

/**
 * @file
 * Contains \Drupal\node\Tests\Migrate\d7\MigrateNodeTest.
 */

namespace Drupal\node\Tests\Migrate\d7;

use Drupal\migrate_drupal\Tests\d7\MigrateDrupal7TestBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Tests node migration.
 *
 * @group node
 */
class MigrateNodeTest extends MigrateDrupal7TestBase {

  static $modules = array('node', 'text', 'filter', 'entity_reference');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installConfig(['node']);
    $this->installSchema('node', ['node_access']);
    $this->installSchema('system', ['sequences']);

    $this->executeMigration('d7_user_role');
    $this->executeMigration('d7_user');
    $this->executeMigration('d7_node_type');
    $this->executeMigration('d7_node__test_content_type');
  }

  /**
   * Asserts various aspects of a node.
   *
   * @param string $id
   *   The node ID.
   * @param string $type
   *   The node type.
   * @param string $langcode
   *   The expected language code.
   * @param string $title
   *   The expected title.
   * @param int $uid
   *   The expected author ID.
   * @param bool $status
   *   The expected status of the node.
   * @param int $created
   *   The expected creation time.
   * @param int $changed
   *   The expected modification time.
   * @param bool $promoted
   *   Whether the node is expected to be promoted to the front page.
   * @param bool $sticky
   *   Whether the node is expected to be sticky.
   */
  protected function assertEntity($id, $type, $langcode, $title, $uid, $status, $created, $changed, $promoted, $sticky) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = Node::load($id);
    $this->assertTrue($node instanceof NodeInterface);
    $this->assertIdentical($type, $node->getType());
    $this->assertIdentical($langcode, $node->langcode->value);
    $this->assertIdentical($title, $node->getTitle());
    $this->assertIdentical($uid, $node->getOwnerId());
    $this->assertIdentical($status, $node->isPublished());
    $this->assertIdentical($created, $node->getCreatedTime());
    if (isset($changed)) {
      $this->assertIdentical($changed, $node->getChangedTime());
    }
    $this->assertIdentical($promoted, $node->isPromoted());
    $this->assertIdentical($sticky, $node->isSticky());
  }

  /**
   * Asserts various aspects of a node revision.
   *
   * @param int $id
   *   The revision ID.
   * @param string $title
   *   The expected title.
   * @param int $uid
   *   The revision author ID.
   * @param string $log
   *   The revision log message.
   * @param int $timestamp
   *   The revision's time stamp.
   */
  protected function assertRevision($id, $title, $uid, $log, $timestamp) {
    $revision = \Drupal::entityManager()->getStorage('node')->loadRevision($id);
    $this->assertTrue($revision instanceof NodeInterface);
    $this->assertIdentical($title, $revision->getTitle());
    $this->assertIdentical($uid, $revision->getRevisionAuthor()->id());
    $this->assertIdentical($log, $revision->revision_log->value);
    $this->assertIdentical($timestamp, $revision->getRevisionCreationTime());
  }

  /**
   * Test node migration from Drupal 7 to 8.
   */
  public function testNode() {
    $this->assertEntity(1, 'test_content_type', 'en', 'A Node', '2', TRUE, '1421727515', '1441032132', TRUE, FALSE);
    $this->assertRevision(1, 'A Node', '2', NULL, '1441032132');
  }

}

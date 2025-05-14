<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @copyright 2014 Modera Foundation
 */
#[ORM\Entity]
#[ORM\Table(name: 'modera_security_session', options: ['collate' => 'utf8mb4_bin', 'charset' => 'utf8mb4'])]
#[ORM\Index(name: 'sess_lifetime_idx', columns: ['sess_lifetime'])]
class Session
{
    /**
     * @var resource
     */
    #[ORM\Id]
    #[ORM\Column(name: 'sess_id', type: 'binary', length: 128)]
    protected $id;

    /**
     * @var resource
     */
    #[ORM\Column(name: 'sess_data', type: 'blob', length: 65532)]
    protected $data;

    #[ORM\Column(name: 'sess_time', type: 'integer', options: ['unsigned' => true])]
    protected int $time;

    #[ORM\Column(name: 'sess_lifetime', type: 'integer', options: ['unsigned' => true])]
    protected int $lifetime;
}

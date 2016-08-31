<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 31/08/16
 * Time: 10:11
 */

namespace FOS2CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Vote as BaseVote;
use FOS\CommentBundle\Model\SignedVoteInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity
 * @ORM\Table(name="fos_comment_vote",uniqueConstraints={@ORM\UniqueConstraint(name="uniq_voter", columns={"comment_id", "voter_id"})})
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @UniqueEntity(
 *     fields={"comment", "voter"}
 * )
 */
class Vote extends BaseVote implements SignedVoteInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Comment of this vote
     *
     * @var Comment
     * @ORM\ManyToOne(targetEntity="FOS2CommentBundle\Entity\Comment")
     */
    protected $comment;

    /**
     * Author of the vote
     *
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @var User
     */
    protected $voter;

    /**
     * Sets the owner of the vote
     *
     * @param string $user
     */
    public function setVoter(UserInterface $voter)
    {
        $this->voter = $voter;
    }

    /**
     * Gets the owner of the vote
     *
     * @return UserInterface
     */
    public function getVoter()
    {
        return $this->voter;
    }

    /**
     * @Assert\Callback
     */
    public function isVoteValid($context)
    {
        if($context instanceof ExecutionContextInterface) {
            if (!$this->checkValue($this->value)) {
                $message = 'A vote cannot have a 0 value';
                $propertyPath = $context->getPropertyPath() . '.value';

                // Validator 2.5 API
                $context->buildViolation($message)
                    ->atPath($propertyPath)
                    ->addViolation();
            }
        } elseif (!$this->checkValue($this->value)) { // For bc
            $message = 'A vote cannot have a 0 value';
            $propertyPath = $context->getPropertyPath() . '.value';

            $context->addViolationAt($propertyPath, $message);
        }
    }
}


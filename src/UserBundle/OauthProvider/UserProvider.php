<?php

namespace UserBundle\OauthProvider;

use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseFOSUBProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Entity\User as Account;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider extends BaseFOSUBProvider
{

    private $doctrine;
    private $container;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager FOSUB user provider.
     * @param array                $properties  Property mapping.
     */
    public function __construct($userManager, array $properties, EntityManager $em, ContainerInterface $container)
    {

        parent::__construct($userManager, $properties);

        $this->doctrine = $em;
        $this->container = $container;

    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {

        $property = $this->getProperty($response);
        $username = $response->getEmail();
        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();

        $user = $this->doctrine->getRepository('UserBundle:User')->findOneBy(array($this->getProperty($response) => $username));

        if (null === $user) {
            $service = $response->getResourceOwner()->getName();




            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';

            $user = new Account();

            $user->$setter_id($username);
            $user->$setter_token($response->getAccessToken());

            //Default Password

            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $password = substr($tokenGenerator->generateToken(), 0, 8);

            if ($service == "facebook") {
                $user->setUsername($response->getEmail());
                $user->setEmail($response->getEmail());
                $user->setPassword($password);

                $user->setFirstname($response->getFirstName());
                $user->setLastname($response->getLastName());

                $user->setNationality('FR');
                $user->setResidentialCountry('FR');

            } elseif($service == "twitter") {

                $nickName = $response->getNickname();

                $user->setUsername($nickName);
                $user->setEmail($response->getEmail());
                $user->setPassword($password);

            }elseif($service == "google") {

                $nickName = strtolower($response->getFirstName()[0] . $response->getLastName());

                $user->setUsername($nickName);
                $user->setEmail($response->getEmail());
                $user->setPassword($password);

                $user->setFirstname($response->getFirstName());
                $user->setLastname($response->getLastName());

            }

            $user->setPro(false);

            $mangopayUser = $this->container->get('mangopay_service')->createNaturalUser($user);

            // Also create wallets
            $wallets = $this->container->get('mangopay_service')->createWallets($mangopayUser->Id);


            // Put mangopay user id and wallets in user entity
            $user->setMangopayUserId($mangopayUser->Id);
            $user->setMangopayBlockedWalletId($wallets['blocked']->Id);
            $user->setMangopayFreeWalletId($wallets['free']->Id);

            $user->setEnabled(true);

            if ($response->getProfilePicture()){
                //$user->setAvatarSocialNetwork($response->getProfilePicture());
            }

            $userValue = $this->doctrine->getRepository('UserBundle:User')->findOneBy(array('username' => $nickName));

            if ($userValue != null) {

                $i = 1;
                do {

                    $newNickname = $nickName . $i;
                    $userValue = $this->doctrine->getRepository('UserBundle:User')->findOneBy(array('username' => $newNickname));
                    $i++;
                } while ($userValue != null);

                $user->setUsername($newNickname);
            }


            $this->doctrine->persist($user);
            $this->doctrine->flush();

            $this->userManager->updateUser($user);
            return $user;
        }
        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);
        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        //update access token
        $user->$setter($response->getAccessToken());

        return $user;
    }

}
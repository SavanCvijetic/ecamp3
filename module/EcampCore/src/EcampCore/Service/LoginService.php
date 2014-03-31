<?php

namespace EcampCore\Service;

use EcampCore\Entity\Autologin;
use EcampCore\Repository\AutologinRepository;
use Zend\Authentication\AuthenticationService;

use EcampCore\Repository\LoginRepository;
use EcampCore\Repository\UserRepository;
use EcampLib\Service\Params\Params;

use EcampCore\Entity\User;
use EcampCore\Entity\Login;
use EcampLib\Service\ServiceBase;

class LoginService
    extends ServiceBase
{
    /**
     * @var \EcampCore\Repository\LoginRepository
     */
    private $loginRepository;

    /**
     * @var \EcampCore\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \EcampCore\Repository\AutologinRepository
     */
    private $autologinRepository;

    public function __construct(
        LoginRepository $loginRepository,
        AutologinRepository $autologinRepository,
        UserRepository $userRepo
    ){
        $this->loginRepository = $loginRepository;
        $this->autologinRepository = $autologinRepository;
        $this->userRepo = $userRepo;
    }

    /**
     * @return \EcampCore\Entity\Login | NULL
     */
    public function Get()
    {
        $user = $this->getMe();

        if (!is_null($user)) {
            return $user->getLogin();
        }

        return null;
    }

    /**
     * @return \EcampCore\Entity\Login
     */
    public function Create(User $user, Params $params)
    {
        $login = new Login($user);
        $loginValdator = new \Core\Validator\Entity\LoginValidator($login);

        $this->validationFailed(
            ! $loginValdator->isValid($params));

        $login->setNewPassword($params->getValue('password'));
        $this->persist($login);

        return $login;
    }

    public function Delete()
    {
        $me = $this->getMe();
        $login = $me->getLogin();

        if (is_null($login)) {
            $this->addValidationMessage("There is no Login to be deleted!");
        } else {
            $this->remove($login);
        }
    }

    /**
     * @return \Zend\Authentication\Result
     */
    public function Login($identifier, $password)
    {
        /** @var \EcampCore\Entity\User  */
        $user = $this->userRepo->findByIdentifier($identifier);

        if (is_null($user)) {
            $login = null;
        } else {
            /** @var \EcampCore\Entity\Login */
            $login = $user->getLogin();
        }

        $authAdapter = new \EcampCore\Auth\LoginPasswordAdapter($login, $password);
        $authService = new AuthenticationService();
        $result = $authService->authenticate($authAdapter);

        return $result;
    }

    public function Logout()
    {
        $authService = new AuthenticationService();
        $authService->clearIdentity();
    }

    public function ResetPassword($pwResetKey, Params $params)
    {
        $login = $this->getLoginByResetKey($pwResetKey);
        $loginValidator = new \EcampCore\Validate\LoginValidator($login);

        if (is_null($login)) {
            $this->addValidationMessage("No Login found for given PasswordResetKey");
        }

        $this->validationFailed(
            ! $loginValidator->isValid($params));

        $login->setNewPassword($params->getValue('password'));
        $login->clearPwResetKey();
    }

    public function ForgotPassword($identifier)
    {
        $user = $this->userRepo->findByIdentifier($identifier);

        if (is_null($user)) {
            return false;
        }

        $login = $user->getLogin();

        if (is_null($login)) {
            return false;
        }

        $login->createPwResetKey();
        $resetKey = $login->getPwResetKey();

        //TODO: Send Mail with Link to Reset Password.
        return $resetKey;
    }

    /**
     * @return \Zend\Authentication\Result
     */
    public function AutoLogin($token)
    {
        $autologin = $this->autologinRepository->findByToken($token);

        $authAdapter = new \EcampCore\Auth\AutologinAdapter($autologin);
        $authService = new AuthenticationService();
        $result = $authService->authenticate($authAdapter);

        return $result;
    }

    /**
     * @param  User   $user
     * @return string
     */
    public function CreateAutoLoginToken(User $user)
    {
        $autoLogin = new AutoLogin($user);
        $this->persist($autoLogin);

        return $autoLogin->createToken();
    }

    /**
     * Returns the LoginEntity with the given pwResetKey
     *
     * @param  string                  $pwResetKey
     * @return \EcampCore\Entity\Login
     */
    private function getLoginByResetKey($pwResetKey)
    {
        /** @var \EcampCore\Entity\Login $login */
        $login = $this->loginRepository->findOneBy(array('pwResetKey' => $pwResetKey));

        return $login;
    }
}

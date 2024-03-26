<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CustomRequest;
use App\Service\Router;
use DI\Container;
use Doctrine\ORM\EntityManager;
use Twig\Environment;

class UserController
{
    private EntityManager $entityManager;
    private Environment $twig;
    private mixed $config;

    public function __construct(protected Container $di)
    {
        $this->entityManager = $this->di->get('entityManager');
        $this->twig = $this->di->get('twig');
        $this->config = $this->di->get('config');
    }

    public function signIn(CustomRequest $request)
    {
        if ($request->isMethod('get')) {
            session_unset();
            $death_time = strtotime('+3 hours');
            $csrfKey = password_hash($this->config['secret_keys']['csrf_key'] . $death_time, PASSWORD_BCRYPT);
            echo $this->twig->render(
                'user-auth-success.twig',
                [
                    'type' => 'Вхід',
                    'typeButton' => 'Увійти',
                    'csrfKey' => $csrfKey
                ]
            );
        } elseif ($request->isMethod('post')) {
            $login = $request->getValue('login');
            $password = $request->getValue('password');
            $csrfKey = $request->getValue('csrfKey');
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['login' => $login, 'password' => md5($password.$this->config['secret_keys']['pass_key'])]);
            if ($user) {
                $death_time = strtotime('+3 hours');
                $_SESSION['csrf'] = ['key' => $csrfKey, 'death_time' => $death_time];
                Router::redirect('/movie/all', $this->config);
            } else {
                echo $this->twig->render(
                    'user-auth-error.twig',
                    [
                        'errorTitle' => 'Користувач не знайдений',
                        'errorText' => 'Перевірте логін і пароль, або зареєтруйтеся',
                        'csrfKey' => $csrfKey
                    ]
                );
            }
        }
    }

    public function signUp(CustomRequest $request)
    {
        if ($request->isMethod('get')) {
            session_unset();
            $death_time = strtotime('+3 hours');
            $csrfKey = password_hash($this->config['secret_keys']['csrf_key'] . $death_time, PASSWORD_BCRYPT);
            echo $this->twig->render(
                'user-auth-success.twig',
                [
                    'type' => 'Реєстрація',
                    'typeButton' => 'Зареєструватися',
                    'csrfKey' => $csrfKey
                ]
            );
        } elseif ($request->isMethod('post')) {
            $login = $request->getValue('login');
            $password = $request->getValue('password');
            $csrfKey = $request->getValue('csrfKey');
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['login' => $login]);
            if ($user) {
                echo $this->twig->render(
                    'user-auth-error.twig',
                    [
                        'errorTitle' => 'Користувач вже існує',
                        'errorText' => "Спробуте інше ім'я",
                        'csrfKey' => $csrfKey
                    ]
                );
            } else {
                $user = new User();
                $user->setLogin($login);
                $user->setPassword(md5($password.$this->config['secret_keys']['pass_key']));
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $death_time = strtotime('+3 hours');
                $_SESSION['csrf'] = ['key' => $csrfKey, 'death_time' => $death_time];
                Router::redirect('/movie/all', $this->config);
            }
        }
    }
}
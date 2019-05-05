<?php

namespace App\Controller;

//get controllers
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

//Define model
use App\JokeModel;


class JokeController
{
    public function index()
    {

       /* $num = config('jokesconf.number_of_jokes');
        $category = config('jokesconf.joke_category');
        $b_url = config('jokesconf.base_url');*/

        $messages = JokeModel::getJokes(10,'','api.icndb.com');

        foreach($messages as $key=>$value) {
            return $this->render('joke.html.twig', ['key' => $key, 'value' => $value]);
        }
    }
}


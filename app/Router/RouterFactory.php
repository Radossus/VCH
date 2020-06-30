<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		//$router->addRoute('mapa', 'Homepage:mapa');
		$router->addRoute('mesto/<url>[/<page>]', 'Mesto:mesto');
        $router->addRoute('strana/<url>[/<page>]', 'Homepage:strana');
        $router->addRoute('blog/<url>[/<page>]', 'Blog:post');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}

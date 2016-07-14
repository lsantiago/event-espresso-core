<?php
namespace EventEspresso\core\services\container;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class OpenCoffeeShop
 * Initialize and configure the CoffeeSop DI container
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         $VID:$
 */
class OpenCoffeeShop {

	/**
	 * @var CoffeeShop $CoffeeShop
	 */
	private $CoffeeShop;

	/**
	 * @var DependencyInjector $DependencyInjector
	 */
	private $DependencyInjector;



	/**
	 * OpenCoffeeShop constructor.
	 */
	public function __construct() {
		// instantiate the container
		$this->CoffeeShop = new CoffeeShop();
		// create a dependency injector class for resolving class constructor arguments
		$this->DependencyInjector = new DependencyInjector(
			$this->CoffeeShop,
			new \EEH_Array()
		);
		// and some coffeemakers, one for creating new instances
		$this->CoffeeShop->addCoffeeMaker(
			new NewCoffeeMaker( $this->CoffeeShop, $this->DependencyInjector ),
			CoffeeMaker::BREW_NEW
		);
		// one for shared services
		$this->CoffeeShop->addCoffeeMaker(
			new SharedCoffeeMaker( $this->CoffeeShop, $this->DependencyInjector ),
			CoffeeMaker::BREW_SHARED
		);
		// and one for classes that only get loaded
		$this->CoffeeShop->addCoffeeMaker(
			new LoadOnlyCoffeeMaker( $this->CoffeeShop, $this->DependencyInjector ),
			CoffeeMaker::BREW_LOAD_ONLY
		);
		// add default recipe, which should handle loading for most PSR-4 compatible classes
		// as long as they are not type hinting for interfaces
		$this->CoffeeShop->addRecipe(
			new Recipe(
				Recipe::DEFAULT_ID
			)
		);
	}



	/**
	 * @return \EventEspresso\core\services\container\CoffeeShop
	 */
	public function CoffeeShop() {
		return $this->CoffeeShop;
	}



	public function addRecipes() {

		// PSR-4 compatible class with aliases
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EventEspresso\core\services\commands\CommandHandlerManager',
				CoffeeMaker::BREW_SHARED,
				array(
					'CommandHandlerManager',
					'CommandHandlerManagerInterface',
					'EventEspresso\core\services\commands\CommandHandlerManagerInterface',
				)
			)
		);
		// PSR-4 compatible class with aliases, which dependency on CommandHandlerManager
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EventEspresso\core\services\commands\CommandBus',
				CoffeeMaker::BREW_SHARED,
				array(
					'CommandBus',
					'CommandBusInterface',
					'EventEspresso\core\services\commands\CommandBusInterface',
				)
			)
		);
		// LEGACY classes that are NOT compatible with PSR-4 autoloading, and so must specify a filepath
		// add a wildcard recipe for loading legacy core interfaces
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EEI_*',
				CoffeeMaker::BREW_LOAD_ONLY,
				array(),
				array(
					EE_INTERFACES . '*.php',
					EE_INTERFACES . '*.interfaces.php',
				)
			)
		);
		// add a wildcard recipe for loading models
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EEM_*',
				CoffeeMaker::BREW_SHARED,
				array(),
				EE_MODELS . '*.model.php'
			)
		);
		// add a wildcard recipe for loading core classes
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EE_*',
				CoffeeMaker::BREW_SHARED,
				array(),
				array(
					EE_CORE . '*.core.php',
					EE_ADMIN . '*.core.php',
					EE_CPTS . '*.core.php',
					EE_CORE . 'data_migration_scripts' . DS . '*.core.php',
					EE_CORE . 'request_stack' . DS . '*.core.php',
					EE_CORE . 'middleware' . DS . '*.core.php',
				)
			)
		);
		// load admin page parent class
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EE_Admin_Page*',
				CoffeeMaker::BREW_LOAD_ONLY,
				array(),
				array( EE_ADMIN . '*.core.php' )
			)
		);
		// add a wildcard recipe for loading core classes
		// $this->CoffeeShop->addRecipe(
		// 	new Recipe(
		// 		'*_Admin_Page',
		// 		CoffeeMaker::BREW_SHARED,
		// 		array(),
		// 		array(
		// 			EE_ADMIN_PAGES . 'transactions' . DS . '*.core.php',
		// 		)
		// 	)
		// );
	}



	public function michaelsTest()
	{
		\EEH_Debug_Tools::printr(__FUNCTION__, __CLASS__, __FILE__, __LINE__, 2);
		echo '<pre style="margin-left:180px;">';
		echo '<h4>addRecipe for obj1</h4>';

		//have one recipe for obj1
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'obj1',
				CoffeeMaker::BREW_SHARED,
				array('filter1'),
				array(),
				'Vendor\Fully\Qualified\ClassName'
			)
		);

		$obj1 = $this->CoffeeShop->brew('obj1', array());
		echo 'brew obj1 directly (should be instance of ClassName):<br>';
		var_dump($obj1);
		$filter1 = $this->CoffeeShop->brew('filter1', array());
		echo 'brew obj1 using filter1 alias (should be instance of ClassName):<br>';
		var_dump($filter1);

		echo '<h4>addRecipe for obj2</h4>';
		//and a different recipe for obj2
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'obj2',
				CoffeeMaker::BREW_SHARED,
				array('filter2'),
				array(),
				'Vendor\Fully\Qualified\ClassName'
			)
		);

		$obj2 = $this->CoffeeShop->brew('obj2', array());
		echo 'brew obj2 directly (should be instance of ClassName):<br>';
		var_dump($obj2);
		$filter2 = $this->CoffeeShop->brew('filter2', array());
		echo 'brew obj2 using filter2 alias (should be instance of ClassName):<br>';
		var_dump($filter2);

		echo '<h4>addRecipe for obj3_uses_obj2</h4>';
		//and then use obj2 in a recipe itself
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'obj3_uses_obj2',
				CoffeeMaker::BREW_SHARED,
				array('obj2'),
				array(),
				'Vendor\Fully\Qualified\ClassName2'
			)
		);

		$obj3_uses_obj2 = $this->CoffeeShop->brew('obj3_uses_obj2', array());
		echo 'brew obj3_uses_obj2 directly (should be instance of ClassName2):<br>';
		var_dump($obj3_uses_obj2);
		$obj2 = $this->CoffeeShop->brew('obj2', array());
		echo 'brew obj2 directly again, which should now be instance of ClassName2, since we set "obj2" as an alias for "obj3_uses_obj2":<br>';
		var_dump($obj2);
		echo '</pre>';
	}



}
// End of file OpenCoffeeShop.php
// Location: /OpenCoffeeShop.php


namespace Vendor\Fully\Qualified;

class ClassName {

}


class ClassName2 {

}
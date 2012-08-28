<?php
namespace Sonata\MediaBundle\Document\ODM;

use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Doctrine\Common\EventSubscriber;

class ProviderSubscriber implements EventSubscriber
{
	/**
	 * @var MediaProviderInterface
	 */
	protected $provider;
	protected $events = array();

	/**
	 * @param MediaProviderInterface $provider
	 */
	public function __construct(MediaProviderInterface $provider)
	{
		$this->provider = $provider;
	}
	
	/**
	 * Proxy event calls to profiler
	 * 
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments)
	{
		if (in_array($name, $this->events))
			$this->provider->$name($arguments[0]->getDocument());
	}
	
	/**
	 * @inheritdoc
	 */
	public function getSubscribedEvents()
	{
		$reflector = new \ReflectionClass('\Doctrine\ODM\MongoDB\Events');
		$methods = get_class_methods($this->provider);
		return $this->events = array_intersect($reflector->getConstants(), $methods);
	}
}
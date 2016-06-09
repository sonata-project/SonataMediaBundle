Admin
=====

The bundle comes with an Admin interface for medias and galleries, that filters them by contexts.


Removing the Admin 
------------------

There may be cases where you don't want the default admins to appear on your dashboard. 

For that, you need to set up a compiler pass that tags the services with ``show_in_dashboard = false``:


.. code-block:: php

  use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
  use Symfony\Component\DependencyInjection\ContainerBuilder;
  use Symfony\Component\DependencyInjection\Definition;
  
  class CompilerPass implements CompilerPassInterface
  {
      public function process(ContainerBuilder $container)
      {

          $definitionsNames = array('sonata.media.admin.media', 'sonata.media.admin.gallery');
  
          foreach ($definitionsNames as $definitionName) {
  
              /** @var Definition $definition */
              $definition = $container->getDefinition($definitionName);
  
              $tags = $definition->getTags();
  
              $tags['sonata.admin'][0]['show_in_dashboard'] = false;
              $definition->setTags($tags);
  
          }
      }
  }



This will keep the services in the container. We need the media service because it is used in the `media widget <../../views/Form/media_widgets.html.twig>`_   to generate an edit link of the media through ``adminByAdminCode('sonata.media.admin.media')`` whenever you have a media in a form in your admin dashboard. 

If you don't want the admin routes to be accessible either, then you will have to remove the service definitions from the container, and override the ``media_widgets`` template and remove the line that generates the url.  

Remove the definitions in your compiler pass:

.. code-block:: php

  $container->removeDefinition('sonata.media.admin.media');
  $container->removeDefinition('sonata.media.admin.gallery');
  $container->removeDefinition('sonata.media.admin.gallery_has_media');
  
Override the SonataMediaBundle and in your new bundle create a template with the same name, at the same location ``/Resources/views/Form/media_widgets.html.twig``, and remove the respective line from it.  

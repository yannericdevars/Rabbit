<?php

namespace DW\RabbitBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class LauncherCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbit:launcher')
            ->setDescription('Lancer une commande')
            ->addArgument('name', InputArgument::REQUIRED, 'Qui voulez vous saluer ??')
            ->addArgument('time', InputArgument::REQUIRED, 'pendant combien de temps ???');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $time = $input->getArgument('time');

        $connection = new AMQPConnection(
            $this->getContainer()->getParameter('rabbitServer'),
            $this->getContainer()->getParameter('rabbitPort'),
            $this->getContainer()->getParameter('rabbitUser'),
            $this->getContainer()->getParameter('rabbitPass'));

        $channel = $connection->channel();


        $channel->queue_declare('task_queue', false, true, false, false);

        $data = $name." ".$time;

        $msg = new AMQPMessage($data,
            array('delivery_mode' => 2) # make message persistent
        );

        $channel->basic_publish($msg, '', 'task_queue');

        echo " [x] Sent ", $data, "\n";

        $channel->close();
        $connection->close();



    }
}
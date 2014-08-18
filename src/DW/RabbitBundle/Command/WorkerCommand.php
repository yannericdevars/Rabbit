<?php

namespace DW\RabbitBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class WorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('rabbit:worker')
            ->setDescription('Lancer un worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = new AMQPConnection(
            $this->getContainer()->getParameter('rabbitServer'),
            $this->getContainer()->getParameter('rabbitPort'),
            $this->getContainer()->getParameter('rabbitUser'),
            $this->getContainer()->getParameter('rabbitPass'));

        $channel = $connection->channel();

        $channel->queue_declare('task_queue', false, true, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        $callback = function($msg){
            echo " [x] Received ", $msg->body, "\n";
            sleep(substr_count($msg->body, '.'));
            echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('task_queue', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        $channel = $connection->channel();

        $channel->queue_declare('task_queue', false, true, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        $callback = function($msg){
            echo " [x] Received ", $msg->body, "\n";
            sleep(substr_count($msg->body, '.'));
            echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('task_queue', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();


    }
}
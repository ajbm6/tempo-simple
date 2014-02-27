<?php

/*
 * This file is part of the TempoSimple project.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TempoSimple\Bundle\SpaghettiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Templating\EngineInterface;
use TempoSimple\Bundle\SpaghettiBundle\Entity\TimeCardRepository;
use TempoSimple\Bundle\SpaghettiBundle\Entity\TimeCard;

class PunchTimeCardCommand extends Command
{
    /** @var TimeCardRepository */
    private $timeCardRepository;

    /**
     * @param TimeCardRepository $timeCardRepository
     */
    public function __construct(
        TimeCardRepository $timeCardrepository
    )
    {
        $this->timeCardrepository = $timeCardrepository;

        parent::__construct();
    }

    /** {@inheritdoc} */
    protected function configure()
    {
        $this->setName('tempo-simple:punch:time-card');
        $this->setAliases(array('punch'));

        $this->addArgument('task', InputArgument::REQUIRED);
        $this->addArgument('start-hour', InputArgument::REQUIRED, 'Format: H:i (e.g. 18:00)');
        $this->addArgument('end-hour', InputArgument::REQUIRED, 'Format: H:i (e.g. 18:15)');

        $this->addOption('project', '-p', InputOption::VALUE_REQUIRED,
            'What project are you working for?', 'Project 1'
        );
        $this->addOption('description', '-D', InputOption::VALUE_REQUIRED,
            'What did you do?', ''
        );
        $this->addOption('date', '-d', InputOption::VALUE_REQUIRED,
            'Format: Y-m-d (e.g. 2014-01-23)', date('Y-m-d')
        );
    }

    /** {@inheritdoc} */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $timeCard = new TimeCard(
            $input->getOption('project'),
            $input->getArgument('task'),
            $input->getOption('date'),
            $input->getArgument('start-hour'),
            $input->getArgument('end-hour'),
            $input->getOption('description')
        );

        $this->timeCardrepository->insert($timeCard);
    }
}

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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TempoSimple\Domain\TimeTracking\Project;
use TempoSimple\Domain\TimeTracking\Task;
use TempoSimple\Domain\TimeTracking\TimeCard;
use TempoSimple\Domain\TimeTracking\Timesheet;

class GenerateBillableReportCommand extends ContainerAwareCommand
{
    /** {@inheritdoc} */
    protected function configure()
    {
        $this->setName('tempo-simple:generate:billable-report');
        $this->setAliases(array('billable'));

        $this->addOption('project', '-p', InputOption::VALUE_REQUIRED, 'The project',
            'Project 1'
        );
        $this->addOption('month', '-m', InputOption::VALUE_REQUIRED,
            'Format: Y-m (e.g. 2014-01)', date('Y-m')
        );
    }

    /** {@inheritdoc} */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $timeCardrepository = $this->getContainer()->get('tempo_simple_spaghetti.time_card_reporitory');
        $templating = $this->getContainer()->get('templating');

        $month = $input->getOption('month');
        $projectName = $input->getOption('project');

        $project = new Project($projectName);

        $timeCards = $timeCardrepository->findBillable($month, $projectName);
        foreach ($timeCards as $timeCard) {
            $taskTitle = $timeCard->getTaskTitle();
            $startHour = $timeCard->getStartHour();
            $endHour = $timeCard->getEndHour();

            $timeCard = new TimeCard($startHour, $endHour);

            if (!$project->hasTask($taskTitle)) {
                $timesheet = new Timesheet();
                $task = new Task($timesheet, $taskTitle);
                $project->addTask($task);
            }
            $task = $project->getTask($taskTitle);
            $task->addTimeCard($timeCard, $taskTitle);
        }

        $view = 'TempoSimpleSpaghettiBundle:Report:billable.md.twig';
        $parameters = array('tasks' => $project->getTasks());

        $output->writeln($templating->render($view, $parameters));
    }
}

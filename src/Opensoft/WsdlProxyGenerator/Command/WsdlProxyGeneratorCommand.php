<?php

/*
 * This file is part of the WSDL Proxy Generator package.
 *
 * Copyright (c) 2012 Farheap Solutions (http://www.farheap.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opensoft\WsdlProxyGenerator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use Opensoft\WsdlProxyGenerator\Generator\SimpleTypeAbstractGenerator;
use Opensoft\WsdlProxyGenerator\Generator\ComplexTypeAbstractGenerator;
use Opensoft\WsdlProxyGenerator\Generator\SimpleTypeGenerator;
use Opensoft\WsdlProxyGenerator\Generator\ComplexTypeGenerator;
use Opensoft\WsdlProxyGenerator\Generator\SoapClientGenerator;
use Opensoft\WsdlProxyGenerator\Generator\SoapServiceGenerator;

/**
 * @author Dmitry Petrov <dmitry.petrov@opensoftdev.ru>
 */
class WsdlProxyGeneratorCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('opensoft:wsdl-proxy:generate')
            ->setDescription('WSDL parser and Proxy generator')
            ->addOption('wsdl', null, InputOption::VALUE_REQUIRED, 'Path to the wsdl file')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'The directory where to create proxy classes')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'The namespace of proxy classes to create')
            ->addOption('license-path', null, InputOption::VALUE_REQUIRED, <<<EOT
The full path to license file.
Its contents will be inserted at the beginning of each generated file
EOT
        )
            ->setHelp(<<<EOT
The <info>opensoft:wsdl-proxy:generate</info> command helps you generates php classes from wsdl file.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction:

<info>php app/console opensoft:wsdl-proxy:generate --namespace=Acme/Wsdl</info>

Note that you can use <comment>/</comment> instead of <comment>\\</comment> for the namespace delimiter to avoid any
problem.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options:

<info>php app/console opensoft:wsdl-proxy:generate --wsdl=simple.wsdl --dir=src --namespace=Acme/Wsdl --no-interaction</info>
EOT
        )
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        $wsdl = Validators::validateWsdl($input->getOption('wsdl'));
        $dir = Validators::validateTargetDir($input->getOption('dir'));
        $namespace = Validators::validateNamespace($input->getOption('namespace'));
        $licensePath = Validators::validateLicensePath($input->getOption('license-path'));

        if ($input->isInteractive()) {
            $this->writeSection($output, array (
                sprintf('Path to the wsdl file: "%s"', $wsdl),
                sprintf('The directory where to create proxy classes: "%s"', $dir),
                sprintf('The namespace of proxy classes to create: "%s"', $namespace)));
            if (!$dialog->askConfirmation($output, $this->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $simpleTypeAbstractGenerator = new SimpleTypeAbstractGenerator($wsdl, $dir . 'SimpleType',
            $namespace . '\\SimpleType', array(), $licensePath);
        $simpleTypeAbstractGenerator->execute();

        $complexTypeAbstractGenerator = new ComplexTypeAbstractGenerator($wsdl, $dir . 'ComplexType',
            $namespace . '\\ComplexType', array($namespace . '\\SimpleType'), $licensePath);
        $complexTypeAbstractGenerator->execute();

        $simpleTypeGenerator = new SimpleTypeGenerator($wsdl, $dir . 'SimpleType',
            $namespace . '\\SimpleType', array(), $licensePath);
        $simpleTypeGenerator->execute();

        $simpleTypeGenerator = new ComplexTypeGenerator($wsdl, $dir . 'ComplexType',
            $namespace . '\\ComplexType', array($namespace . '\\SimpleType'), $licensePath);
        $simpleTypeGenerator->execute();

        $soapClientGenerator = new SoapClientGenerator($wsdl, $dir,
            $namespace, array('RuntimeException', 'SoapFault', 'SoapClient as BaseSoapClient'), $licensePath);
        $soapClientGenerator->execute();

        $soapServiceGenerator = new SoapServiceGenerator($wsdl, $dir, $namespace,
            array('RuntimeException', 'SoapFault',
                'SoapClient as BaseSoapClient',
                'InvalidArgumentException'),
            $licensePath);
        $soapServiceGenerator->execute();

    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        $wsdl = $dialog->askAndValidate(
            $output, $this->getQuestion('Please enter a path to the wsdl file'),
            array('Opensoft\WsdlProxyGenerator\Command\Validators', 'validateWsdl'), false, $input->getOption('wsdl')
        );
        $input->setOption('wsdl', $wsdl);

        $directory = $dialog->askAndValidate(
            $output, $this->getQuestion('Please enter a directory where to create proxy classes'),
            array('Opensoft\WsdlProxyGenerator\Command\Validators', 'validateTargetDir'), false, $input->getOption('dir')
        );
        $input->setOption('dir', $directory);

        $namespace = $dialog->askAndValidate(
            $output, $this->getQuestion('Please enter a namespace of proxy classes to create'),
            array('Opensoft\WsdlProxyGenerator\Command\Validators', 'validateNamespace'), false, $input->getOption('namespace')
        );
        $input->setOption('namespace', $namespace);

        $licensePath = $dialog->askAndValidate(
            $output, $this->getQuestion('Please enter a path to license file. If you do not want to fill out - leave blank'),
            array('Opensoft\WsdlProxyGenerator\Command\Validators', 'validateLicensePath'), false, $input->getOption('license-path')
        );
        $input->setOption('license-path', $licensePath);
    }

    /**
     * Returns formatted string/question
     *
     * @param $question
     * @param $default
     * @param string $sep
     * @return string
     */
    private function getQuestion($question, $default = null,  $sep = ':')
    {
        return $default ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep)
                        : sprintf('<info>%s</info>%s ', $question, $sep);
    }

    /**
     * Returns formatted string/block
     *
     * @param OutputInterface $output
     * @param $text
     * @param string $style
     */
    public function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }
}

<?php

namespace Handlebars\Helpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;
use Traversable;

class EachHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $index = 0;
        $positionalArgs = $args->getPositionalArguments();
        $tmp = $context->get($positionalArgs[0]);
        if (isset($positionalArgs[1])) {
            $index = $context->get($positionalArgs[1]);
            if (isset($tmp[$index])) {
                $tmp = $tmp[$index];
                if (isset($positionalArgs[2])) {
                    $index = $context->get($positionalArgs[1]);
                }
            }
        }
        $buffer = '';

        if (!$tmp) {
            $template->setStopToken('else');
            $template->discard();
            $template->setStopToken(false);
            $buffer = $template->render($context);
        }
        elseif (is_array($tmp) || $tmp instanceof Traversable) {
            $size = count($tmp);
            $isList = is_array($tmp) && (array_keys($tmp) === range(0, $size - 1));
            $lastIndex = $isList ? (count($tmp) - 1) : false;

            foreach ($tmp as $key => $var) {
                $specialVariables = array(
                    '@size'          => $size,
                    '@index'         => $index,
                    '@first'         => ($index === 0),
                    '@last'          => ($index === $lastIndex),
                    '@itemlistsize'  => $size,
                    '@itemlistindex' => $index,
                    '@itemlistfirst' => ($index === 0),
                    '@itemlistlast'  => ($index === $lastIndex),
                    '@item'          => $var
                );
                if (!$isList) {
                    $specialVariables['@key'] = $key;
                }
                $context->pushSpecialVariables($specialVariables);
                $context->push($var);
                $template->setStopToken('else');
                $template->rewind();
                $buffer .= $template->render($context);
                $context->pop();
                $context->popSpecialVariables();
                $index++;
            }

            $template->setStopToken(false);
        }

        return $buffer;
    }
}

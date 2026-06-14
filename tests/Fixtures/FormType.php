<?php

namespace Repat\CliCrud\Tests\Fixtures;

enum FormType: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}

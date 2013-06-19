<?php

namespace Mawelous\YamopLaravel;

/**
 * Represents object fetched from database
 * Sets mapper class name to YamopLaravel\Mapper
 *
 * @author Kamil Zieliński <kamilz@mawelous.com>
 */
class Model extends \Mawelous\Yamop\Model
{
	protected static $_mapperClassName = 'Mapper';
}

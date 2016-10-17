<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Security;

/**
 * @author Pavel Janda <me@paveljanda.com>
 */
interface IDatabaseAuthenticator
{
	/**
	 * @param  string $username
	 * @param  string $password
	 */
	public function authenticate($username, $password, $authenticateUser = TRUE);
}
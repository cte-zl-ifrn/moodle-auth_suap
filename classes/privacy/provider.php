<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Privacy provider for auth_suap.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_suap\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\context;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_progress;
use core_privacy\local\request\userlist;
use core_privacy\local\request\userlist_context;
use core_privacy\local\request\writer;

/**
 * Privacy API implementation for auth_suap.
 *
 * This plugin does not store user data directly in Moodle's database beyond
 * what is stored in the standard user table (name, email, username, etc).
 *
 * However, this plugin communicates with an external SUAP service and sends
 * user information during authentication and data synchronization processes.
 *
 * @package     auth_suap\privacy
 */
class provider implements metadata_provider {
    /**
     * Return the fields which contain personal data.
     *
     * @param   collection $items a reference to the collection to use to store the metadata.
     * @return  collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items): collection {
        // Describe what user data is sent to the external SUAP service.
        $items->add_external_location_link(
            'suap',
            [
                'username' => 'privacy:metadata:suap:username',
                'email' => 'privacy:metadata:suap:email',
                'firstname' => 'privacy:metadata:suap:firstname',
                'lastname' => 'privacy:metadata:suap:lastname',
                'cpf' => 'privacy:metadata:suap:cpf',
                'tipo' => 'privacy:metadata:suap:tipo',
            ],
            'privacy:metadata:suap:explanation'
        );

        return $items;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Tara
 * Date: 13/5/15
 * Time: 11:39 AM
 *
 *
 */

class Mcommon extends CI_Model
{
    function __construct()
    {
        parent::__construct();

    }
    function clean_mysqli_connection( $dbc )
    {
        while( mysqli_more_results($dbc) )
        {
            if(mysqli_next_result($dbc))
            {
                $result = mysqli_use_result($dbc);
                if(!empty($result)  && (get_class($result) == 'mysqli_stmt') )
                {
                    mysqli_stmt_free_result($result);
                }
                else
                {
                    unset($result);
                }
            }
        }
    }
}
?>
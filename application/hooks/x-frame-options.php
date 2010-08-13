<?php
function x_frame_options_init()
{
    header('X-Frame-Options: DENY');
}
Event::add('system.ready', 'x_frame_options_init');

<?php 
if (!class_exists('Event_Espresso_Widget')) {
	class Event_Espresso_Widget extends WP_Widget {
		function Event_Espresso_Widget() {

			/* Widget settings. */

			$widget_options = array( 'classname' => 'events', 'description' => __('A widget to display your upcoming events.', 'events') );
			/* Widget control settings. */

			$control_options = array( 'width' => 300, 'height' => 350, 'id_base' => 'events-widget' );
			/* Create the widget. */

			$this->WP_Widget( 'events-widget', __('Event Espresso Widget', 'events'), $widget_options, $control_options );

		}
		function widget($args, $instance ) {

			extract( $args );

			global $wpdb, $org_options;
			/* Our variables from the widget settings. */

			$title = apply_filters('widget_title', $instance['title'] );
							
			/* Before widget (defined by themes). */
			echo $before_widget;

			/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title )
				echo $before_title . $title . $after_title;
				
				if ($instance['category_name'] != ''){
					$type = 'category';
				}

					$event_page_id =$org_options['event_page_id'];
					
					$show_expired = $instance['show_expired'] == 'false' ? " AND (e.start_date >= '".date ( 'Y-m-d' )."' OR e.event_status = 'O' OR e.registration_end >= '".date ( 'Y-m-d' )."') " : '';
					$show_secondary = $instance['show_secondary'] == 'false' ? " AND e.event_status != 'S' " : '';
					$show_deleted = $instance['show_deleted'] == 'false' ? " AND e.event_status != 'D' " : '';
					$show_recurrence = $instance['show_recurrence'] == 'false' ? " AND e.recurrence_id = '0' " : '';
					$limit = $instance['limit'] > 0 ? " LIMIT 0," . $instance['limit'] . " " : ' LIMIT 0,5 ';
					//$order_by = $order_by != 'NULL'? " ORDER BY ". $order_by ." ASC " : " ORDER BY date(start_date), id ASC ";
					$order_by = " ORDER BY date(start_date), id ASC ";
			
					if ($type == 'category'){
						$sql = "SELECT e.*, c.category_name, c.category_name, c.category_desc FROM " . EVENTS_CATEGORY_TABLE . " c ";
						$sql .= " JOIN " . EVENTS_CATEGORY_REL_TABLE . " r ON r.cat_id = c.id ";
						$sql .= " JOIN " . EVENTS_DETAIL_TABLE . " e ON e.id = r.event_id ";
						$sql .= " WHERE c.id = '" . $instance['category_name'] . "' ";
						$sql .= " AND e.is_active = 'Y' ";
					}else{
						$sql = "SELECT e.* FROM " . EVENTS_DETAIL_TABLE . " e ";
						$sql .= " WHERE e.is_active = 'Y' ";
					}
					$sql .= $show_expired;
					$sql .= $show_secondary;
					$sql .= $show_deleted;
					$sql .= $show_recurrence;
					$sql .= $order_by;
					$sql .= $limit;
	

					$events = $wpdb->get_results($sql);

					//print_r($events);
					//event_espresso_get_event_details($sql);
						foreach ($events as $event)
						{
							// set display to TRUE
							$display_event = TRUE;
							// check if this is a recurring event
							if ( $event->recurrence_id ) {
								// get the recurrence id for the event
								$r = get_recurrence( $event->recurrence_id );	
								// if the recurrence start date is not the same as the event start date, then this is not the first event
								if ( $r->recurrence_start_date != $event->start_date ) {
									$display_event = FALSE;
								}				
							}
					
							if ( $display_event ) {
							
								$event_id = $event->id;
								$event_name = $event->event_name;
								$start_date = $event->start_date;
								$category_name = $event->category_name !=''?$event->category_name:'';
								$category_desc = $event->category_desc !=''?$event->category_desc:'';
								$externalURL = $event->externalURL;
								$registration_url = $externalURL != '' ? $externalURL : home_url() . '/?page_id='.$event_page_id.'&regevent_action=register&event_id='. $event_id . '&name_of_event=' . stripslashes_deep($event_name);
								
								if ( isset( $event->venue_city ) && $event->venue_city != '' )
									$event_city = $event->venue_city . ', ';
								elseif ( isset ( $event->city ) && $event->city != '')
									$event_city = $event->city . ', ';
								else
									$event_city = '';
									
								if ( isset ( $event->venue_state ) && $event->venue_state != '')
									$event_state = $event->venue_state . ' - ';
								elseif ( isset ( $event->state ) && $event->state != '')
										$event_state = $event->state . ' - ';
								else
										$event_state = '';
	
							//Print out the array of event status options
								//print_r (event_espresso_get_is_active($event_id));
								
								//Here we can create messages based on the event status
								$status = event_espresso_get_is_active($event_id);
								$status_display = ' - ' . $status['display_custom'];
								$status_display_ongoing = $status['status'] == 'ONGOING'? ' - ' . $status['display_custom']:'';
								$status_display_deleted = $status['status'] == 'DELETED'? ' - ' . $status['display_custom']:'';
								$status_display_secondary = $status['status'] == 'SECONDARY'? ' - ' . $status['display_custom']:'';
								$status_display_reg_closed = $status['status'] == 'REGISTRATION_CLOSED'? ' - ' . $status['display_custom']:'';
								$status_display_not_open = $status['status'] == 'REGISTRATION_NOT_OPEN'? ' - ' . $status['display_custom']:'';
								$status_display_open = $status['status'] == 'REGISTRATION_OPEN'? ' - ' . $status['display_custom']:'';
								
								//You can also display a custom message. For example, this is a custom registration not open message:
								$status_display_custom_closed = $status['status'] == 'REGISTRATION_CLOSED'? ' - <span class="espresso_closed">'.__('Regsitration is Closed','event_espresso').'</span>':'';
								
								if (!is_user_logged_in() && get_option('events_members_active') == 'true' && $member_only == 'Y') {
									//Display a message if the user is not logged in.
									 //_e('Member Only Event. Please ','event_espresso') . event_espresso_user_login_link() . '.';
								}else{
									//Serve up the event list
									//As of version 3.0.17 the event list details have been moved to event_list_display.php
									switch ($status['status']){
										case 'NOT_ACTIVE':
										//Don't show the event if any of the above are true
										break;
											
										default:
											?>
								<li class="upcmng-evnt-wdgt-li-item">
									<h6 class="upcmng-evnt-wdgt-hdr-link"><a href="<?php echo $registration_url;?>"><?php echo stripslashes_deep($event_name)?></a></h6> 
									<span class="upcmng-evnt-wdgt-date"><?php echo $event_city . $event_state; ?><em><?php echo event_date_display($start_date)?></em></span>
									<?php /* These are custom messages that can be displayed based on the event status. Just comment the one you want to use. */?>
									<?php //echo $status_display; //Turn this on to display the overall status of the event. ?>
									<?php //echo $status_display_ongoing; //Turn this on to display the ongoing message. ?>
									<?php //echo $status_display_deleted; //Turn this on to display the deleted message. ?>
									<?php //echo $status_display_secondary; //Turn this on to display the secondary message. ?>
									<?php //echo $status_display_reg_closed; //Turn this on to display the secondary message. ?>
									<?php //echo $status_display_not_open; //Turn this on to display the secondary message. ?>
									<?php //echo $status_display_open; //Turn this on to display the secondary message. ?>
									<?php //echo $status_display_custom_closed; //Turn this on to display the secondary message. ?>
								</li>
											<?php
										break;
										}
									}
								}  // end 	if ( $display_event ) 		
							}
			/* After widget (defined by themes). */
			echo $after_widget;

			}

		/* Update the widget settings. */
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			/* Strip tags for title and name to remove HTML (important for text inputs). */
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['category_name'] = $new_instance['category_name'];
			$instance['show_expired'] = $new_instance['show_expired'];
			$instance['show_secondary'] = $new_instance['show_secondary'];
			$instance['show_deleted'] = $new_instance['show_deleted'];
			$instance['show_recurrence'] = $new_instance['show_recurrence'];
			$instance['limit'] = $new_instance['limit'];

			return $instance;

		}

		/**
		 * Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function
		 * when creating your form elements. This handles the confusing stuff.
		 **/
		function form( $instance ) {

			/* Set up some default widget settings. */

			$defaults = array( 'title' => __('Upcoming Events', 'events'), 'category_name' => '', 'show_expired' => __('false', 'events'), 'show_secondary' => __('false', 'events'), 'show_deleted' => __('false', 'events'), 'show_recurrence' => __('false', 'events') );

			$instance = wp_parse_args( (array) $instance, $defaults ); 
			
			$values=array(					
				array('id'=>'false','text'=> __('No','event_espresso')),
				array('id'=>'true','text'=> __('Yes','event_espresso')));
				//select_input('allow_multiple', $values, $allow_multiple);
			?>

<!-- Widget Title: Text Input -->

<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>">
    <?php _e('Title:', 'Upcoming Events'); ?>
  </label>
  <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" size="20" type="text" />
</p>
<p> <label for="<?php echo $this->get_field_id( 'category_name' ); ?>">
    <?php _e('Event Category:', 'event_espresso'); ?>
  </label><br />
 <?php echo espresso_db_dropdown(id, category_name, EVENTS_CATEGORY_TABLE, id, $instance['category_name'], $strMethod="desc", $this->get_field_name( 'category_name' )) ?></p>
 <p>
  <label for="<?php echo $this->get_field_id( 'limit' ); ?>">
    <?php _e('Limit:', 'event_espresso'); ?>
  </label>
  <input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $instance['limit']; ?>" size="3" type="text" />
</p>
<p><strong><?php _e('Optional Settings:', 'event_espresso'); ?></strong></p>
 <p><?php _e('Show Expired Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_expired' ), $values, $instance['show_expired']);?></p>
 <p><?php _e('Show Secondary Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_secondary' ), $values, $instance['show_secondary']);?></p>
 <p><?php _e('Show Deleted Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_deleted' ), $values, $instance['show_deleted']);?></p>
 <p><?php _e('Show Recurring Events?', 'event_espresso'); ?> <?php echo select_input($this->get_field_name( 'show_recurrence' ), $values, $instance['show_recurrence']);?></p>
 
<?php
		}

	}

}
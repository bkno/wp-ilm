jQuery( function( $ ) {
	var LD_Notifications = {
		init: function() {
			this.toggle_child_input();
			this.toggle_shortcode_instruction();
			this.submit_disabled_fields();
			this.init_child_field();
			this.init_select2_fields();
			this.init_filter_child_field();
			this.init_filter_select2_fields();
		},

		build_select2_args: function( args ) {
			return {
				dropdownAutoWidth: true,
				minimumInputLength: args.hasOwnProperty( 'minimumInputLength' ) ? args.minimumInputLength : null,
				theme: 'learndash',
				width: '100%',
				data: args.hasOwnProperty( 'data' ) && typeof args.data === 'object' && args.data.constructor === Array ? args.data : null,
				disabled: args.hasOwnProperty( 'disabled' ) ? args.disabled : false,
				placeholder: args.hasOwnProperty( 'placeholder' ) ? args.placeholder : null,
				ajax: args.hasOwnProperty( 'ajax' ) ? args.ajax : {
					url: LD_Notifications_String.ajaxurl,
					dataType: 'json',
					delay: 250,
					type: 'POST',
					data: function( params ) {
						return {
							action: 'ld_notifications_get_posts_list',
							nonce: LD_Notifications_String.nonce,
							post_type: args.post_type,
							keyword: params.term,
							course_id: args.hasOwnProperty( 'course_id' ) ? args.course_id : null,
							lesson_id: args.hasOwnProperty( 'lesson_id' ) ? args.lesson_id : null,
							topic_id: args.hasOwnProperty( 'topic_id' ) ? args.topic_id : null,
							quiz_id: args.hasOwnProperty( 'quiz_id' ) ? args.quiz_id : null,
							parent_type: args.hasOwnProperty( 'parent_type' ) ? args.parent_type : null,
							parent_id: args.hasOwnProperty( 'parent_id' ) ? args.parent_id : null,
						}
					},
					processResults: function( data ) {
						const results =  {
							results: data,
						}

						return results;
					}
				}
			};
		},

		toggle_child_input: function() {
			if ( $( '.ld_notifications_metabox_settings' ).length > 0 ) {
				$( 'select[name="_ld_notifications_trigger"]' ).on( 'change', function( e ) {
					LD_Notifications.update_select_values();

					var option_class = $( this ).val();

					$( '.sfwd_input.' + option_class ).show();
					$( '.sfwd_input.child-input' ).not( '.' + option_class ).hide();
					$( '.sfwd_input.hide_on' ).show();
					$( '.sfwd_input.hide_on_' + option_class ).hide();
				});

				$( window ).on( 'load', function( e ) {
					LD_Notifications.update_select_values_onload();

					var option_class = $( 'select[name="_ld_notifications_trigger"]' ).val();

					$( '.sfwd_input.' + option_class ).show();
					$( '.sfwd_input.child-input' ).not( '.' + option_class ).hide();
					$( '.sfwd_input.hide-empty-select' ).hide();
					$( '.sfwd_input.hide_on' ).show();
					$( '.sfwd_input.hide_on_' + option_class ).hide();
				});
			}
		},

		toggle_shortcode_instruction: function() {
			if ( $( '.shortcodes-instruction' ).length > 0 ) {
				$( 'select[name="_ld_notifications_trigger"]' ).on( 'change', function( e ) {
					var option_class = $( this ).val();

					$( '.shortcodes-instruction.' + option_class ).show();
					$( '.shortcodes-instruction' ).not( '.' + option_class ).hide();

					if ( option_class == 'complete_course' ) {
						$( '.additional-help-text.complete_course' ).show();
					}
				});

				$( window ).load( function( e ) {
					var option_class = $( 'select[name="_ld_notifications_trigger"]' ).val();
					$( '.shortcodes-instruction.' + option_class ).show();

					if ( option_class == 'complete_course' ) {
						$( '.additional-help-text.complete_course' ).show();
					}
				});
			}
		},

		submit_disabled_fields: function() {
			$( 'form' ).on( 'submit', function() {
				$( this ).find( ':input' ).prop( 'disabled', false );
			});
		},

		init_child_field: function() {
			$( '.parent_field select' ).on( 'change', function( e ) {
				var parent_type = '',
					child_post_type = '';

				const el = $( this ),
					parent_id  = $( this ).val(),
					name = $( this ).attr( 'name' );

				const course_id = $( 'select[name="_ld_notifications_course_id"]' ).val(),
					lesson_id = $( 'select[name="_ld_notifications_lesson_id"]' ).val(),
					topic_id = $( 'select[name="_ld_notifications_topic_id"]' ).val(),
					quiz_id = $( 'select[name="_ld_notifications_quiz_id"]' ).val();
				
				switch ( name ) {
					case '_ld_notifications_course_id':
						parent_type = 'course';
						child_post_type = 'sfwd-lessons'
						break;
					case '_ld_notifications_lesson_id':
						parent_type = 'lesson';
						child_post_type = 'sfwd-topic'
						break;
					case '_ld_notifications_topic_id':
						parent_type = 'topic';
						child_post_type = 'sfwd-quiz'
						break;
				}

				const child_field_select2_args = {
					post_type: child_post_type,
					course_id: course_id,
					lesson_id: lesson_id,
					topic_id: topic_id,
					quiz_id: quiz_id,
					parent_type: parent_type,
					parent_id: parent_id,
					minimumInputLength: 0,
					disabled: false,
				};

				if ( el.attr( 'name' ).indexOf( 'course' ) != '-1' ) {
					$( 'select[name="_ld_notifications_topic_id"]' ).html(
						'<option value="">' + LD_Notifications_String.select_lesson_first + '</option>'
					).attr( 'disabled', true );

					$( 'select[name="_ld_notifications_quiz_id"]' ).html(
						'<option value="">' + LD_Notifications_String.select_topic_first + '</option>'
					).attr( 'disabled', true );

					$( 'select[name="_ld_notifications_lesson_id"]' ).html( 
						'<option value="">' + LD_Notifications_String.select_lesson + '</option>'
						+ '<option value="all">' + LD_Notifications_String.all_lessons + '</option>'
					);

					$( 'select[name="_ld_notifications_lesson_id"]' ).select2( 'destroy' ).select2( LD_Notifications.build_select2_args( child_field_select2_args ) );
				}

				if ( el.attr( 'name' ).indexOf( 'lesson' ) != '-1' ) {
					$( 'select[name="_ld_notifications_quiz_id"]' ).html(
						'<option value="">' + LD_Notifications_String.select_topic_first + '</option>'
					).attr( 'disabled', true );

					$( 'select[name="_ld_notifications_topic_id"]' ).html( 
						'<option value="">' + LD_Notifications_String.select_topic + '</option>' +
						'<option value="all">' + LD_Notifications_String.all_topics + '</option>'
					);

					$( 'select[name="_ld_notifications_topic_id"]' ).select2( 'destroy' ).select2( LD_Notifications.build_select2_args( child_field_select2_args ) );
				}

				if ( el.attr( 'name' ).indexOf( 'topic' ) != '-1' ) {
					$( 'select[name="_ld_notifications_quiz_id"]' ).html( 
						'<option value="">' + LD_Notifications_String.select_quiz + '</option>' +
						'<option value="all">' + LD_Notifications_String.all_quizzes + '</option>' 
					);

					$( 'select[name="_ld_notifications_quiz_id"]' ).select2( 'destroy' ).select2( LD_Notifications.build_select2_args( child_field_select2_args ) );
				}
			});
		},

		init_select2_fields: function() {
			$( '.sfwd_input.dynamic-options select' ).each( function( $el, index ) {
				const wrapper = $( this ).closest( '.sfwd_input' ),
					id = wrapper.attr( 'id' ).trim(),
					disabled_child = wrapper.hasClass( 'disabled-child' ) ? true : false;
					
				let post_type = false;

				switch ( id ) {
					case 'group_id':
						post_type = 'groups';
						break;

					case 'course_id':
						post_type = 'sfwd-courses';
						break;
					
					case 'lesson_id':
						post_type = 'sfwd-lessons';
						break;

					case 'topic_id':
						post_type = 'sfwd-topic';
						break;

					case 'quiz_id':
						post_type = 'sfwd-quiz';
						break;
				}

				$( this ).select2( LD_Notifications.build_select2_args( { 
					post_type: post_type,
					minimumInputLength: 0,
					disabled: disabled_child,
				} ) );
			});
		},

		init_filter_child_field: function() {
			$( '#posts-filter select.select2' ).on( 'change', function( e ) {
				var parent_type = '',
					child_post_type = '';

				const el = $( this ),
					parent_id  = $( this ).val(),
					name = $( this ).attr( 'name' );

				const course_id = $( 'select[name="course_id"]' ).val(),
					lesson_id   = $( 'select[name="lesson_id"]' ).val(),
					topic_id    = $( 'select[name="topic_id"]' ).val(),
					quiz_id     = $( 'select[name="quiz_id"]' ).val();
				
				switch ( name ) {
					case 'course_id':
						parent_type = 'course';
						child_post_type = 'sfwd-lessons'
						break;
					case 'lesson_id':
						parent_type = 'lesson';
						child_post_type = 'sfwd-topic'
						break;
					case 'topic_id':
						parent_type = 'topic';
						child_post_type = 'sfwd-quiz'
						break;
				}

				const child_field_select2_args = {
					post_type: child_post_type,
					course_id: course_id,
					lesson_id: lesson_id,
					topic_id: topic_id,
					quiz_id: quiz_id,
					parent_type: parent_type,
					parent_id: parent_id,
					minimumInputLength: 0,
					disabled: false,
				};

				if ( el.attr( 'name' ).indexOf( 'course' ) != '-1' ) {
					$( '#posts-filter select[name="topic_id"]' ).html(
						'<option value="">' + LD_Notifications_String.select_lesson_first + '</option>'
					).attr( 'disabled', true );

					$( '#posts-filter select[name="quiz_id"]' ).html(
						'<option value="">' + LD_Notifications_String.select_topic_first + '</option>'
					).attr( 'disabled', true );

					$( '#posts-filter select[name="lesson_id"]' ).html( 
						'<option value="">' + LD_Notifications_String.select_lesson + '</option>'
						+ '<option value="all">' + LD_Notifications_String.all_lessons + '</option>'
					);

					$( '#posts-filter select[name="lesson_id"]' ).select2( 'destroy' ).select2( LD_Notifications.build_select2_args( child_field_select2_args ) );
				}

				if ( el.attr( 'name' ).indexOf( 'lesson' ) != '-1' ) {
					$( '#posts-filter select[name="quiz_id"]' ).html(
						'<option value="">' + LD_Notifications_String.select_topic_first + '</option>'
					).attr( 'disabled', true );

					$( '#posts-filter select[name="topic_id"]' ).html( 
						'<option value="">' + LD_Notifications_String.select_topic + '</option>' +
						'<option value="all">' + LD_Notifications_String.all_topics + '</option>'
					);

					$( '#posts-filter select[name="topic_id"]' ).select2( 'destroy' ).select2( LD_Notifications.build_select2_args( child_field_select2_args ) );
				}

				if ( el.attr( 'name' ).indexOf( 'topic' ) != '-1' ) {
					$( '#posts-filter select[name="quiz_id"]' ).html( 
						'<option value="">' + LD_Notifications_String.select_quiz + '</option>' +
						'<option value="all">' + LD_Notifications_String.all_quizzes + '</option>' 
					);

					$( '#posts-filter select[name="quiz_id"]' ).select2( 'destroy' ).select2( LD_Notifications.build_select2_args( child_field_select2_args ) );
				}
			});
		},

		init_filter_select2_fields: function() {
			$( '#posts-filter select.select2' ).each( function( el, index ) {
				const id = $( this ).attr( 'id' ).trim(),
					disabled_child = $( this ).hasClass( 'disabled-child' ) ? true : false;
					
				let post_type = false,
					placeholder;

				switch ( id ) {
					case 'group_id':
						post_type = 'groups';
						placeholder = LD_Notifications_String.select_group;
						break;
						
					case 'course_id':
						post_type = 'sfwd-courses';
						placeholder = LD_Notifications_String.select_course;
						break;
						
					case 'lesson_id':
						post_type = 'sfwd-lessons';
						placeholder = LD_Notifications_String.select_course_first;
						break;
						
					case 'topic_id':
						post_type = 'sfwd-topic';
						placeholder = LD_Notifications_String.select_lesson_first;
						break;
						
					case 'quiz_id':
						post_type = 'sfwd-quiz';
						placeholder = LD_Notifications_String.select_topic_first;
						break;
				}

				$( this ).select2( LD_Notifications.build_select2_args( { 
					post_type: post_type,
					minimumInputLength: 0,
					disabled: disabled_child,
					placeholder: placeholder,
				} ) );
			});
		},

		update_select_values: function() {
			$( 'select[name="_ld_notifications_lesson_id"]' ).html( 
				'<option value="">' + LD_Notifications_String.select_course_first + '</option>'
			);
			
			$( 'select[name="_ld_notifications_topic_id"]' ).html(
				'<option value="">' + LD_Notifications_String.select_lesson_first + '</option>'
			);

			$( 'select[name="_ld_notifications_quiz_id"]' ).html(
				'<option value="">' + LD_Notifications_String.select_topic_first + '</option>'
			);
		},

		update_select_values_onload: function() {
			if ( $( 'select[name="_ld_notifications_course_id"]' ).val() === '' ) {
				$( 'select[name="_ld_notifications_course_id"]' ).prop( 'selectedIndex', 0 );
			}

			if ( $( 'select[name="_ld_notifications_lesson_id"]' ).val() === '' ) {
				$( 'select[name="_ld_notifications_lesson_id"]' ).html( 
					'<option value="">' + LD_Notifications_String.select_course_first + '</option>'
				);
			}
			
			if ( $( 'select[name="_ld_notifications_topic_id"]' ).val() === '' ) {
				$( 'select[name="_ld_notifications_topic_id"]' ).html(
					'<option value="">' + LD_Notifications_String.select_lesson_first + '</option>'
				);
			}

			if ( $( 'select[name="_ld_notifications_quiz_id"]' ).val() === '' ) {
				$( 'select[name="_ld_notifications_quiz_id"]' ).html(
					'<option value="">' + LD_Notifications_String.select_topic_first + '</option>'
				);
			}
		}
	};

	LD_Notifications.init();

} );
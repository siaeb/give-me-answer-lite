<?php

defined( 'ABSPATH' ) || exit;

/**
 * Add dashboard menu
 *
 * @access public
 * @since 1.0
 * @version 1.0
 * @package give-me-answer
 * @author Siavash Ebrahimi <vbnetgenius@gmail.com>
 */
class GMA_Dashboard {

    /**
     * Menu output
     *
     * @access public
     * @since 1.0
     * @return void
     */
	static function display() {
		$statistics  = gma_statistics();
		$last_questions = gma_lite()->statistics->last_questions( 7 );
		$last_comments = gma_lite()->statistics->last_comments( 7 );
		?>
		<div class="gma gma-dashboard">
			<div class="container-fluid">
				<!-- Summary -->
				<div class="row">
					<!-- Questions -->
					<div class="col-6 col-md-3">
						<div class="card card-stats p-0">
							<div class="card-body p-0">
								<div class="row align-items-center justify-content-between mx-0 pr-3 gma-question-widget">
									<div class="gma-widget-icon p-3"><i class="fa fa-question fa-large"></i></div>
									<div>
                                        <div class="gma-widget-count text-center"><?php echo number_format( $statistics['questions'] ); ?></div>
                                        <div class="gma-widget-description"><?php _e('Questions', 'give-me-answer-lite'); ?></div>
                                    </div>
								</div>
							</div>
						</div>
					</div>
					<!-- Answers -->
					<div class="col-6 col-md-3">
						<div class="card card-stats p-0">
							<div class="card-body p-0">
								<div class="row align-items-center justify-content-between mx-0 pr-3 gma-answer-widget">
									<div class="gma-widget-icon p-3"><i class="fa fa-reply fa-large"></i> </div>
									<div>
                                        <div class="gma-widget-count text-center"><?php echo number_format( $statistics['answers'] ); ?></div>
                                        <div class="gma-widget-description"><?php _e('Answers', 'give-me-answer-lite'); ?></div>
                                    </div>
								</div>
							</div>
						</div>
					</div>
					<!-- Comments -->
					<div class="col-6 col-md-3">
						<div class="card card-stats p-0">
							<div class="card-body p-0">
								<div class="row align-items-center justify-content-between mx-0 pr-3 gma-comment-widget">
									<div class="gma-widget-icon p-3"><i class="fa fa-comment fa-large"></i></div>
									<div>
                                        <div class="gma-widget-count text-center"><?php echo number_format( $statistics['comments'] ); ?></div>
                                        <div class="gma-widget-description"><?php _e('Comments', 'give-me-answer-lite'); ?></div>
                                    </div>
								</div>
							</div>
						</div>
					</div>
					<!-- Users -->
					<div class="col-6 col-md-3">
						<div class="card card-stats p-0">
							<div class="card-body p-0">
								<div class="row align-items-center justify-content-between mx-0 pr-3 gma-user-widget">
									<div class="gma-widget-icon p-3"><i class="fa fa-users fa-large"></i></div>
									<div>
                                        <div class="gma-widget-count text-center"><?php echo number_format( $statistics['users'] ); ?></div>
                                        <div class="gma-widget-description"><?php _e('Users', 'give-me-answer-lite'); ?></div>
                                    </div>
								</div>
							</div>
						</div>
					</div>
				</div>

                <div class="row">

                    <div class="col-md-8">
                        <div class="card gma-dashboard-widget px-0">
                            <div class="card-body pt-0 px-0">
                                <h5 class="card-title mb-4 p-3">
					                <?php _e('Questions', 'give-me-answer-lite'); ?>
                                </h5>
                                <div class="gma-chart-container" style="height: 200px;">
                                    <canvas id="ctx_questions_stat_by_month"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- questions stat-->
                    <div class="col-sm-6 col-md-4">
                        <div class="card gma-dashboard-widget px-0">
                            <div class="card-body pt-0 px-0">
                                <h5 class="card-title mb-4 p-3">
                                    <?php _e('Questions status', 'give-me-answer-lite'); ?>
                                </h5>
                                <canvas id="ctx_questions_stat" class="m-auto" width="200px" height="200px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last questions and answers -->
				<div class="row">
					<div class="col-md-6">
						<!-- Last questions -->
						<div class="card gma-dashboard-widget px-0">
							<div class="card-body pt-0 px-0">
								<h5 class="card-title mb-4 p-3"><?php _e('Last questions', 'give-me-answer-lite'); ?></h5>
								<div class="gma-questions px-3">
                                    <?php foreach ( $last_questions as $question ) { ?>
                                        <div class="gma-question d-flex align-items-center justify-content-start py-1">
                                            <div class="d-flex align-items-center gma-user text-nowrap">
                                                <img width="35" height="35" class="rounded-circle" src="<?php echo gma_get_user_image($question->question_author_ID); ?>">
                                                <div>
                                                    <a class="gma-question-author ml-1" target="_blank" href="<?php echo gma_get_user_questions_url( $question->question_author_ID ); ?>">
		                                                <?php echo esc_html( $question->question_author ); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="gma-date text-nowrap px-3"><?php echo human_time_diff(strtotime($question->question_date)); ?></div>
                                            <div class="gma-status mr-2">
                                                <?php if ( $question->question_status === 'pending' ) { ?>
                                                    <span class="badge badge-warning pt-2 pb-1"><?php _e('Pending', 'give-me-answer-lite'); ?></span>
                                                <?php } else if ( $question->question_status === 'publish' ) { ?>
                                                    <span class="badge badge-success pt-2 pb-1"><?php _e('Published', 'give-me-answer-lite'); ?></span>
                                                <?php } else { ?>
                                                    <?php do_action( 'gma_dashboard_widget_last_questions_status', $question->question_status ); ?>
                                                <?php } ?>
                                            </div>
                                            <div class="gma-qutitle text-truncate">
                                                <a href="<?php echo get_permalink( $question->question_id ); ?>" target="_blank">
                                                    <?php echo esc_html( $question->question_title ); ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php } ?>
								</div>
							</div>
						</div>
					</div>
                    <div class="col-md-6">
                        <!-- Last questions -->
                        <div class="card gma-dashboard-widget px-0">
                            <div class="card-body pt-0 px-0">
                                <h5 class="card-title mb-4 p-3">
                                    <?php _e('Last comments', 'give-me-answer-lite'); ?>
                                </h5>
                                <div class="gma-comments px-3">
									<?php foreach ( $last_comments as $comment ) { ?>
                                        <div class="gma-comment d-flex align-items-center justify-content-start py-1">
                                            <div class="d-flex align-items-center gma-user text-nowrap">
                                                <img width="35" height="35" class="rounded-circle" src="<?php echo gma_get_user_image($comment->comment_author_ID); ?>">
                                                <div>
                                                    <a class="gma-comment-author ml-1" target="_blank" href="<?php echo gma_get_user_questions_url( $comment->comment_author_ID ); ?>">
														<?php echo esc_html( $comment->comment_author ); ?>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="gma-date text-nowrap px-3">
                                                <?php echo human_time_diff(strtotime($comment->comment_date)); ?>
                                            </div>
                                            <div class="gma-qutitle text-truncate">
                                                <a href="<?php echo get_permalink( gma_get_question_id($comment->comment_post_ID) ) . '#comment-' . $comment->comment_id; ?>" target="_blank">
													<?php echo esc_html( $comment->comment_content ); ?>
                                                </a>
                                            </div>
                                        </div>
									<?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>

			</div>
		</div>
		<?php
	}

}
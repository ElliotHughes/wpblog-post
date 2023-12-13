<?php

// Include the file where the function is defined
require_once './function.php';

// Create a new test class
class WpblogPostHandleCommentTest
{

    // Test case for wpblog_post_handle_comment function
    public function testWpblogPostHandleComment() {
        // Set up test data
        $comment_text = "Test comment";
        $comment_ID = 1;
        $comment = (object) array(
            'comment_author_IP' => '123.456.789.001',
            'comment' => 'Test comment'
        );
        $show_comment_location = true;
        $city = 'New York';

        // Set up expected output
        $expected_output = $comment_text . '<div class="post-comment-location"><span class="dashicons dashicons-location"></span>' . esc_html__( 'From', 'wpblog-post' ) . '' . $city . '</div>' . $comment_ID;

        // Call the function with test data
        $output = wpblog_post_handle_comment($comment_text);

        // Check if the output matches the expected output
        $this->assertEquals($expected_output, $output);
    }
}

// Create a new instance of the test class
$test = new WpblogPostHandleCommentTest();

// Run the test case
$test->testWpblogPostHandleComment();
<?php

/**
 * Plugin Name: WPGraphQL Mutations
 * Description: Adds WPGraphQL Mutations
 * Author: Alexandra Spalato
 * Author URI: http://alexandraspalato.com/
 * Version: 1.0
 * Text Domain: wp-graphql-mutations
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}



add_action('graphql_register_types', function () {
    register_graphql_mutation('voteMutation', [
        'inputFields' => [
            'emailInput' => [
                'type' => 'String',
                'description' => __('Email Field', 'twentynineteen')
            ],
            'messageInput' => [
                'type' => 'String',
                'description' => __('Message Field', 'twentynineteen')
            ],
            'votesInput' => [
                'type' => ['list_of' => 'ID'],
                'description' => __('List of Votes Titles', 'twentynineteen')
            ]

        ],
        'outputFields' => [
            'voteSubmitted' => [
                'type' => 'Boolean',
                'description' => __('Vote successfull or not', 'twentynineteen'),
            ]
        ],
        'mutateAndGetPayload' => function ($input, $context, $info) {
            // wp_send_json($input); //for debugging in graphiql

            //validate the input: make sure email is valid, do we need 3 items absolutely
            if (!is_email($input['emailInput'])) {
                throw new \GraphQL\Error\UserError('The email is invalid');
            }
            $existing_vote = get_page_by_title($input['emailInput'], 'OBJECT', 'votes');
            if ($existing_vote) {
                throw new \GraphQL\Error\UserError('You have already submitted a vote from this email');
            }
            $post_id = wp_insert_post([
                'post_type' => 'votes',
                'post_title' => sanitize_text_field($input['emailInput']),
                'post_content' => sanitize_text_field($input['messageInput']),
                'post_status' => 'publish'

            ]);
            update_field('stories_votes', $input['votesInput'], $post_id);
            return  [
                "voteSubmitted" => true
            ];
        }
    ]);
});
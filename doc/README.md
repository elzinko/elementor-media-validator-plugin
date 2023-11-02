# Elementor Media Validator Plugin

Welcome to the Elementor Media Validator Plugin! This plugin extends the functionality of the Elementor page builder by adding a media validation system. It allows agencies and clients to validate media files directly within the WordPress dashboard.

## Features

- **Media Validation**: Users can mark media files as validated or not validated.
- **Roles and Capabilities**: Custom roles for client and agency with specific capabilities related to media validation.
- **Logging System**: Every action taken on media files is logged for accountability and tracking.
- **Notifications**: Automated email notifications to clients or administrators when a media file's status changes.
- **Easy Integration**: Seamlessly integrates with Elementor and WordPress media library.

## Installation

1. Download the `elementor-media-validator-plugin.zip` file.
2. Go to your WordPress dashboard, navigate to the Plugins section, and click 'Add New'.
3. Upload the zip file through the 'Upload Plugin' button.
4. Activate the plugin after the upload is complete.

## Setup

After activation, the plugin will create two new roles: 'Client' and 'Agency'. You can assign these roles to users through the WordPress Users menu.

### Assigning Roles

Navigate to Users > All Users, select a user, and then choose the appropriate role from the 'Role' dropdown.

## Usage

### For Agencies

- **Validate Media**: Go to Media > Media Validator, select the media you want to validate, click the checkbox, and submit.
- **Review Logs**: Access the Logger under Media > Media Validator > Logger to review all validation actions.

### For Clients

- **View Validations**: Clients can view the status of their media files in the Media Validator tab.
- **Submit Comments**: When validating a media file, clients can submit comments for the agency's review.

## Customization

The plugin provides hooks and filters for developers to extend its functionality.


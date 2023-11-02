<?php

function emvp_create_client_role() {
    add_role('emvp_client', 'Media validator - Client', array(
        'read' => true, // Permet de lire
        'emvp_access_validator' => true,
        'emvp_access_settings' => false,
        'emvp_access_logger' => false,
        'emvp_access_export' => false,
        )
    );
}

function emvp_create_agency_role() {
    add_role('emvp_agency', 'Madia validator - Agency', array(
        'read' => true, // Permet de lire
        'emvp_access_validator' => true,
        'emvp_access_settings' => true,
        'emvp_access_logger' => true,
        'emvp_access_export' => true,
        )
    );
}

function emvp_remove_client_role() {
    remove_role('emvp_client');
}

function emvp_remove_agency_role() {
    remove_role('emvp_agency');
}
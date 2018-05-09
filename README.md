![Logo of the project](https://www.paymentexpress.com/Image/pxlogoclear.png)

# Payment Express enrolments for Moodle
> Course enrolments via PxPay 2.0 payment gateway

[![Build Status](https://travis-ci.org/catalyst/moodle-enrol_paymentexpress.svg?branch=master)](https://travis-ci.org/catalyst/moodle-enrol_paymentexpress)

This Moodle enrolment plugin gives the course administrator the ability to
set up courses where payment is required by learners in order to gain access
to the course. Payments are handled securely by the Payment Express payment gateway,
removing this burden from Moodle - https://www.paymentexpress.com

## Installing

1. Install the Moodle plugin as you would any other plugin, by extracting the plugin
into the `/enrol` directory, i.e your plugint should live here: `/enrol/paymentexpress`.

2. Run the Moodle upgrade

3. Enable plugin via `Site administration ► Plugins ► Enrolments ► Manage enrol plugins`

## Configuration

Go to `Site administration ► Plugins ► Enrolments ► Payment Express` to
configure the default settings for this plugins.

You're now ready to add this enrolment method to relevant courses.

## Contributing and development <3

If you'd like to contribute, please fork the repository and use a feature
branch. Pull requests are warmly welcome!

When developing, add `$CFG->debugpxpay` to your `config.php` file in order to hit
Payment Express's UAT servers for testing/debugging.

## Acknowledgements
The development of this plugin was kindly funded by Family Planning New Zealand :)

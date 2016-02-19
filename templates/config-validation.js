/**
 * Created by neil on 16/02/2016.
 */

$(document).ready(function() {
    $('#config-builder-form').on('submit', function() {
        //remove all errors
        $('.input-error').removeClass('input-error');
        $('.formerror').remove();

        var errors = {};

        //validate private_dirs, absolute path
        var private_dirs = $('#private_dirs').val();
        if (private_dirs.substring(0,1) !== '/')
        {
            errors['private_dirs'] = 'Must begin with /';
        }

        //validate dns_tools_dirs, absolute path
        var dns_tools_dir = $('#dns_tools_dir').val();
        if (dns_tools_dir.substring(0,1) !== '/')
        {
            errors['dns_tools_dir'] = 'Must begin with /';
        }

        //validate support_email, valid email
        var support_email = $('#support_email').val();
        if(!valid_email(support_email))
        {
            errors['support_email'] = 'Enter a valid email address';
        }

        // validate tiny dns, must be valid ip innit
        var tinydns_ip = $('#tinydns_ip').val();
        if(!valid_ip(tinydns_ip))
        {
            errors['tinydns_ip'] = 'Enter a valid IP address';
        }

        //validate records per page, must be an integer innit
        var records_per_page = $('#records_per_page').val();
        if (isNaN(records_per_page))
        {
            errors['records_per_page'] = 'Must be a number/integer';
        }

        //validate timeout, must be an integer innit
        var timeout = $('#timeout').val();
        if (isNaN(timeout) || timeout == '')
        {
            errors['timeout'] = 'Must be a number/integer';
        }

        //validate local url, check begins with http:// first
        var local_url = $('#local_url').val();
        if (local_url.substring(0, 7) === 'http://')
        {
            // check remainder of the string if it is valid domain or ip
            var domain_ip = local_url.substring(7);
            if (!valid_domain(domain_ip) && !valid_ip(domain_ip))
            {
                errors['local_url'] = 'Must be a valid domain or IP';
            }
        }
        else
        {
            errors['local_url'] = 'Must begin with http://';
        }

        // finally, check all required fields! This will overwrite above errors for each field if set
        $('.required').each(function() {
            if ($(this).val() == "")
            {
                var field_id = $(this).attr('id');
                errors[field_id] = 'Enter a value';
            }
        });

        if (Object.keys(errors).length > 0)
        {
            for (var field in errors)
            {
                var str = '<span class="formerror">' + errors[field] + '</span>';
                $('#'+field).addClass('input-error').before(str);
            }
            return false;
        }
    });
});

function valid_ip(ipaddress)
{
    if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress))
    {
        return (true)
    }
    return (false)
}

function valid_domain(domain) {
    if (/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})$/.test(domain))
    {
        return (true);
    }
    return (false);
}

function valid_email(email) {
    if (/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email))
    {
        return (true)
    }
    return (false)
}
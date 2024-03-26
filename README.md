# <p align=center>synology-ddns-gandi</p>
Add the Gandi DNS provider to Synology DDNS service. 

This is a native integration. The advantage over a task scheduled every X hours is that, as soon as the IP change, the Synology's DDNS service updates it immediately. As a result, the NAS is always reachable. This cannot be ensured with a periodic update.

I provide two modules, an IPv4 only version, which is supposed to be the only possibility with the current DSM release. But I also provide a IPv4 + IPv6 version which fetches the IPv6 of the default gateway. Therefore, you do not have the choice like with the IPv4. Consequently, if the IPv6 change, but not the IPv4, the record will not be updated. However, the service will update both IPs once every 24 hours.

If the record does not exist in the DNS zone, it will be created for you. Also, the script keeps the TTL you configured through the Gandi web admin interface. 

Since the information required are not standard, the field names in the Synology interface do not necessarily match the inputs. But I explain everything in the setup section. 

The installation is simple, you download and write the code directly from GitHub using curl.

## Installation
1. Connect to the NAS using ssh
2. Switch to root user: `sudo -s`
3. Install the module: 
   1. IPv4 only: `curl -w "\n" https://raw.githubusercontent.com/EmixamPP/synology-ddns-gandi/main/gandi.php > /usr/syno/bin/ddns/gandi.php`
   2. IPv4 + IPv6: `curl -w "\n" https://raw.githubusercontent.com/EmixamPP/synology-ddns-gandi/main/gandi_ipv6.php > /usr/syno/bin/ddns/gandi.php`
4. Update file permissions: `chmod 755 /usr/syno/bin/ddns/gandi.php`
5. Add it to the provider list: `curl -w "\n" https://raw.githubusercontent.com/EmixamPP/synology-ddns-gandi/main/ddns_provider.conf >> /etc.defaults/ddns_provider.conf`

## Setup
1. Obtain your Gandi personal access token (pat) from your Gandi account ([see doc](https://api.gandi.net/docs/authentication/)).
2. For example, if your your fully qualified domain name (fqdn) is: example.com 
3. And the subdomain that you want to redirect is all: @
4. Then fill the Synology DDNS configuration as follows:
      ![](.github/screenshots/example.png)

## :heart: Thanks to
* [Pinpin31](https://github.com/Pinpin31) for migrating the deprecated Api Key authentication to Personal Access Token.
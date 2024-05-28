# Superlinks

TL-DR; Superlinks is a http/dns based link shortener similar to golinks, it uses SAML2 for authentication through apache mellon.

## How it works
This system relies on several components:
1. You have a domain and that you use some short hostname to refer to the system/container running superlinks (for convenience I will assume it to be "go" in the rest of the README)
2. The client devices have your domain set as their search domain
3. The client browser is either configured to use short hostnames or aware specifically of the superlinks hostname.
  a. For firefox there is a script included which adds a host specific setting, currently hardcoded to use "go"
  b. For chromium after the fisrt visit to go/something in the past things would "just work"
  c. I was having trouble with safari that I never resolved (golinks seems to just solve this by using a browserextension)
4. For authentication superlinks relies on SAML2 through apache2 with mellon, it could be that other servers would also work, I just haven't tested this.

When a user goes to `a/something` if this is a known superlink that the user has access to the user will either be forwarded to that which it points at or presented with multiple options if s/he has access to multiple versions of `something` (thus namesquatting is impossible).

For public links (without login) currently a user would go to `go/public/something`, this can probably be changed to `go/something` by having the public script handle 403 errors and is an improvement that I hope to implement at some point.

Superlinks have several access levels:
1. private - only the owner can access it
2. domain - all domain users can access the link
3. public - anyone can use it
4. extended - not implemented yet, meant to allow adding more finegrained tuning of who can access a link

## License
GPL3

## Authors
- Keeper-of-the-Keys
- shbedev

// Encode a JWT with expiration time to 1 week
function encodeJWT(payload, secret) {
    // Set the expiration time to 1 week from the current time (in seconds)
    const expirationTime = Math.floor(Date.now() / 1000) + (7 * 24 * 60 * 60); // 1 week in seconds

    // Add the expiration time to the payload
    payload.exp = expirationTime;

    // Encode the header and payload
    const header = { alg: 'HS256', typ: 'JWT' };
    const headerEncoded = btoa(JSON.stringify(header));
    const payloadEncoded = btoa(JSON.stringify(payload));

    // Create the signature
    const signature = btoa(headerEncoded + '.' + payloadEncoded + '.' + secret);

    // Return the JWT token
    return headerEncoded + '.' + payloadEncoded + '.' + signature;
}


// Decode a JWT
function decodeJWT(token, secret) {
    const [headerEncoded, payloadEncoded, signature] = token.split('.');
    const header = JSON.parse(atob(headerEncoded));
    const payload = JSON.parse(atob(payloadEncoded));

    // Verify the signature (optional but recommended)
    const expectedSignature = btoa(headerEncoded + '.' + payloadEncoded + '.' + secret);
    if (signature !== expectedSignature) {
        // throw new Error('Invalid token signature');
        return false;
    }

    // Check the expiration time
    if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
        // throw new Error('Token has expired');
        logout();
        return false;
    }
    return payload;
}

function getToken(){
    return localStorage.getItem('jwtToken');
}


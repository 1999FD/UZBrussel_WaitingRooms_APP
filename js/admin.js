// Simulated login function
function login(username, password, secret) {
    const user = {
        username: username,
        password: password,
        role: 'admin'
    };
    // Create a JWT token with the user information
    const token = encodeJWT(user, secret);
    // Store the token in local storage or session storage
    localStorage.setItem('jwtToken', token);
    return token;
}

// Simulated logout function
function logout() {
    // Clear the JWT token from local storage or session storage
    localStorage.removeItem('jwtToken');
}

// Simulated function to get user information from JWT
function getUserFromToken() {
    const token = localStorage.getItem('jwtToken');
    if (token) {
        try {
            // Decode the token and extract the payload
            const payload = decodeJWT(token, secret);
            // Return user information from the payload
            return payload;
        } catch (error) {
            console.error('Error decoding token:', error.message);
            return null;
        }
    } else {
        return null;
    }
}

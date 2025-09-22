console.log('🧪 Remember Token Test Helper');
console.log('================================');

const cookies = document.cookie.split(';').map(c => c.trim());
console.log('📋 Current Cookies:');
cookies.forEach(cookie => {
  if (cookie.includes('session') || cookie.includes('remember') || cookie.includes('laravel')) {
    console.log(`  ✅ ${cookie}`);
  }
});

console.log('\n🏪 Local Storage Auth:');
Object.keys(localStorage).forEach(key => {
  if (key.includes('auth') || key.includes('user') || key.includes('token')) {
    console.log(`  🔑 ${key}: ${localStorage.getItem(key)}`);
  }
});

console.log('\n🔄 Session Test Instructions:');
console.log('1. Note the cookies above');
console.log('2. Close this browser tab/window completely');
console.log('3. Reopen and navigate back to your app');
console.log('4. If remember=true worked, you should stay logged in');
console.log('5. Run this script again to compare cookies');
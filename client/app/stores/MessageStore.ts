export const useMessageStore = defineStore('message', () => {
    
  const message = ref('LOADING...');

  const setMessage = (newMessage: string) => {
    message.value = newMessage;
  }
  
  return {
    message,
    setMessage
  };
});
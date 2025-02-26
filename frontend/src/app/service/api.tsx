import axios, { AxiosInstance } from 'axios';
import Cookies from 'js-cookie';

class Api {
  api: AxiosInstance;

  constructor() {

    this.api = axios.create({
      baseURL: 'http://127.0.0.1:8000',
    });

    this.api.interceptors.request.use((config) => {
      const token = Cookies.get('token') || localStorage.getItem('token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    }, (error) => Promise.reject(error));

    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error?.response?.status === 401) {
          Cookies.remove('token');
          localStorage.removeItem('token');
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
    );

    this.api.defaults.headers.common['Content-Type'] = 'application/json';
    this.api.defaults.headers.common['Accept'] = 'application/json';
    //this.api.defaults.withCredentials = true;
  }
  async get(url: string): Promise<any> {
    return await this.api.get(url);
  }

  async post(url: string, data: any): Promise<any> {
    return await this.api.post(url, data);
  }

  async put(url: string, data: any): Promise<any> {
    return await this.api.put(url, data);
  }

  async patch(url: string): Promise<any> {
    return await this.api.patch(url);
  }

  async delete(url: string): Promise<any> {
    return await this.api.delete(url);
  }

  async getOne(url: string, id: number): Promise<any> {
    return await this.api.get(url + id);
  }


}

export default new Api();
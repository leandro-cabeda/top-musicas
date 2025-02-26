import { render, act } from "@testing-library/react";
import AuthContext, { AuthProvider } from "../AuthContext";

describe("AuthContext", () => {
  it("deve permitir login e logout", async () => {
    let login: any, logout: any, user : any;

    render(
      <AuthProvider>
        <AuthContext.Consumer>
          {(value) => {
            login = value.login;
            logout = value.logout;
            user = value.user;
            return null;
          }}
        </AuthContext.Consumer>
      </AuthProvider>
    );

    expect(user).toBe(null);

    await act(async () => {
      await login("leandro.admin@hotmail.com", "123456");
    });

    expect(user).not.toBe(null);

    act(() => {
      logout();
    });

    expect(user).toBe(null);
  });
});

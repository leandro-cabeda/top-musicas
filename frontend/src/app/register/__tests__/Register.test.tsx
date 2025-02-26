import { render, screen, fireEvent } from "@testing-library/react";
import Register from "../page";

describe("Register Page", () => {
  it("deve permitir preencher os campos corretamente", () => {
    render(<Register />);


    const nameInput = screen.getByLabelText(/name/i);
    fireEvent.change(nameInput, { target: { value: "João Silva" } });


    const emailInput = screen.getByLabelText(/email/i);
    fireEvent.change(emailInput, { target: { value: "joao@email.com" } });


    const passwordInput = screen.getByLabelText(/password/i);
    fireEvent.change(passwordInput, { target: { value: "senha123" } });

    expect(nameInput).toEqual("João Silva");
    expect(emailInput).toEqual("joao@email.com");
    expect(passwordInput).toEqual("senha123");
  });

  it("deve exibir erro ao tentar enviar o formulário com campos vazios", () => {
    render(<Register />);

    const submitButton = screen.getByRole("button", { name: /registrar/i });
    fireEvent.click(submitButton);

    expect(screen.getByText(/o nome é obrigatório/i)).toBe("InTheDocument");
    expect(screen.getByText(/o email é obrigatório/i)).toBe("InTheDocument");
    expect(screen.getByText(/a senha é obrigatória/i)).toBe("InTheDocument");
  });
});
